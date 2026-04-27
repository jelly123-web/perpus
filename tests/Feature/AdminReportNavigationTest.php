<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminReportNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_report_permission_can_open_report_page_and_see_relative_links(): void
    {
        $permission = Permission::query()->updateOrCreate([
            'name' => 'view_reports',
        ], [
            'label' => 'Laporan',
        ]);

        $role = Role::query()->updateOrCreate([
            'name' => 'petugas',
        ], [
            'label' => 'Petugas',
        ]);

        $role->permissions()->sync([$permission->id]);

        $user = User::factory()->create([
            'name' => 'Petugas',
            'username' => 'petugas',
            'email' => 'petugas@example.com',
            'role_id' => $role->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('admin.reports.index', [], false));

        $response->assertOk();
        $response->assertSee(route('admin.reports.export', ['format' => 'excel'], false), false);
        $response->assertSee(route('admin.reports.index', [], false), false);
    }

    public function test_guest_is_redirected_to_login_for_report_page(): void
    {
        $response = $this->get(route('admin.reports.index', [], false));

        $response->assertRedirect(route('login', [], false));
    }
}
