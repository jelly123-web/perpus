<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;

class AuthGoogleLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_redirects_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }

    public function test_login_page_shows_google_button(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertSee(route('auth.google.redirect'), false);
        $response->assertSee('aria-label="Masuk dengan Google"', false);
        $response->assertSee('username');
    }

    public function test_google_callback_creates_user_and_logs_in(): void
    {
        config()->set('services.google.client_id', 'test-client-id');
        config()->set('services.google.client_secret', 'test-client-secret');
        config()->set('services.google.redirect', 'http://localhost/auth/google/callback');

        $siswaRole = Role::query()->create([
            'name' => 'siswa',
            'label' => 'Siswa',
        ]);

        $googleUser = new SocialiteUser();
        $googleUser->id = 'google-user-123';
        $googleUser->name = 'Google User';
        $googleUser->email = 'google.user@example.com';

        $provider = \Mockery::mock(Provider::class);
        $provider->shouldReceive('user')->once()->andReturn($googleUser);

        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('profile.show'));
        $this->assertAuthenticated();

        $user = User::query()->where('email', 'google.user@example.com')->first();

        $this->assertNotNull($user);
        $this->assertSame('google-user-123', $user->google_id);
        $this->assertSame($siswaRole->id, $user->role_id);
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_login_cannot_use_nik(): void
    {
        $user = User::query()->create([
            'name' => 'User NIK',
            'username' => 'userktp',
            'nik' => '3173012301010001',
            'email' => 'userktp@example.com',
            'is_active' => true,
            'password' => 'secret123',
        ]);

        $response = $this->post(route('login.authenticate'), [
            'username' => '3173012301010001',
            'password' => 'secret123',
        ]);

        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_google_callback_links_existing_user_by_email(): void
    {
        config()->set('services.google.client_id', 'test-client-id');
        config()->set('services.google.client_secret', 'test-client-secret');
        config()->set('services.google.redirect', 'http://localhost/auth/google/callback');

        $siswaRole = Role::query()->create([
            'name' => 'siswa',
            'label' => 'Siswa',
        ]);

        $existingUser = User::query()->create([
            'name' => 'Existing User',
            'username' => 'existing.user',
            'email' => 'existing.user@example.com',
            'role_id' => $siswaRole->id,
            'is_active' => true,
            'password' => 'secret123',
        ]);

        $googleUser = new SocialiteUser();
        $googleUser->id = 'google-existing-123';
        $googleUser->name = 'Existing User';
        $googleUser->email = 'existing.user@example.com';

        $provider = \Mockery::mock(Provider::class);
        $provider->shouldReceive('user')->once()->andReturn($googleUser);

        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('profile.show'));
        $this->assertAuthenticatedAs($existingUser->fresh());
        $this->assertSame('google-existing-123', $existingUser->fresh()->google_id);
    }
}
