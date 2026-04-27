<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureUserHasPermission;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppNameColorSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_change_app_name_color_and_it_renders_in_layouts(): void
    {
        $this->withoutMiddleware(EnsureUserHasPermission::class);

        $adminRole = Role::query()->create([
            'name' => 'admin',
            'label' => 'Admin',
        ]);

        $admin = User::factory()->create([
            'name' => 'Admin',
            'username' => 'admin',
            'email' => 'admin@example.com',
            'role_id' => $adminRole->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->put(route('admin.settings.update'), [
            'app_name' => 'LibraVault',
            'app_name_color' => '#0F4C5C',
            'show_app_name' => 1,
        ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('settings', [
            'key' => 'app_name_color',
            'value' => '#0F4C5C',
        ]);

        $this->assertSame('#0F4C5C', Setting::valueOr('app_name_color'));

        $page = $this->actingAs($admin)->get(route('admin.settings.index'));
        $page->assertOk();
        $page->assertSee('style="color: #0F4C5C"', false);
    }
}
