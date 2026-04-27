<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Http\Middleware\EnsureUserHasPermission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class UserEmailReuseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'services.recaptcha' => [
                'site_key' => 'test-site-key',
                'secret_key' => 'test-secret-key',
            ],
        ]);
        Http::fake([
            'www.google.com/recaptcha/api/siteverify' => Http::response([
                'success' => true,
            ]),
        ]);
    }

    public function test_register_accepts_an_email_used_by_another_account(): void
    {
        Notification::fake();
        $this->withoutMiddleware(ValidateCsrfToken::class);

        User::factory()->create([
            'name' => 'Akun Lama',
            'username' => 'akun_lama',
            'email' => 'shared@example.com',
            'is_active' => true,
        ]);

        $response = $this->post(route('register.store'), [
            'name' => 'Akun Baru',
            'email' => 'shared@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'g-recaptcha-response' => 'test-token',
        ]);

        $response->assertRedirect(route('verification.notice'));
        $this->assertAuthenticated();
        $this->assertSame(2, User::query()->where('email', 'shared@example.com')->count());
    }

    public function test_profile_update_accepts_duplicate_email(): void
    {
        $user = User::factory()->create([
            'name' => 'Pemilik',
            'username' => 'pemilik',
            'email' => 'owner@example.com',
            'is_active' => true,
        ]);

        User::factory()->create([
            'name' => 'Pengguna Lain',
            'username' => 'pengguna_lain',
            'email' => 'shared@example.com',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Pemilik',
            'username' => 'pemilik',
            'email' => 'shared@example.com',
            'phone' => null,
            'kelas' => null,
            'jurusan' => null,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertSame(2, User::query()->where('email', 'shared@example.com')->count());
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'shared@example.com',
        ]);
    }

    public function test_admin_can_store_user_with_duplicate_email(): void
    {
        $this->withoutMiddleware(EnsureUserHasPermission::class);

        $adminRole = Role::query()->create([
            'name' => 'admin',
            'label' => 'Admin',
        ]);
        $studentRole = Role::query()->create([
            'name' => 'siswa',
            'label' => 'Siswa',
        ]);

        $admin = User::factory()->create([
            'name' => 'Admin',
            'username' => 'admin',
            'email' => 'admin@example.com',
            'role_id' => $adminRole->id,
            'is_active' => true,
        ]);

        User::factory()->create([
            'name' => 'Pengguna Lama',
            'username' => 'pengguna_lama',
            'email' => 'shared@example.com',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'User Baru',
            'username' => 'user_baru',
            'email' => 'shared@example.com',
            'role_id' => $studentRole->id,
            'password' => 'secret123',
            'is_active' => true,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertSame(2, User::query()->where('email', 'shared@example.com')->count());
        $this->assertDatabaseHas('users', [
            'username' => 'user_baru',
            'email' => 'shared@example.com',
        ]);
    }

    public function test_admin_import_uses_username_as_the_user_match_key(): void
    {
        $this->withoutMiddleware(EnsureUserHasPermission::class);

        $adminRole = Role::query()->create([
            'name' => 'admin',
            'label' => 'Admin',
        ]);
        Role::query()->create([
            'name' => 'siswa',
            'label' => 'Siswa',
        ]);

        $admin = User::factory()->create([
            'name' => 'Admin',
            'username' => 'admin',
            'email' => 'admin@example.com',
            'role_id' => $adminRole->id,
            'is_active' => true,
        ]);

        $existingUser = User::factory()->create([
            'name' => 'Existing User',
            'username' => 'existing_user',
            'email' => 'shared@example.com',
            'is_active' => true,
        ]);

        $csv = implode("\n", [
            'name,username,email,role',
            'Imported User,imported_user,shared@example.com,siswa',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.users.import'), [
            'import_file' => UploadedFile::fake()->createWithContent('users.csv', $csv),
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', [
            'username' => 'imported_user',
            'email' => 'shared@example.com',
        ]);
        $this->assertSame('Existing User', $existingUser->fresh()->name);
    }
}
