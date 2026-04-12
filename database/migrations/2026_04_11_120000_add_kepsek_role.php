<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('roles')->updateOrInsert(
            ['name' => 'kepsek'],
            ['label' => 'Kepala Sekolah', 'created_at' => $now, 'updated_at' => $now]
        );

        $roleId = DB::table('roles')->where('name', 'kepsek')->value('id');
        $permissionIds = DB::table('permissions')
            ->whereIn('name', ['access_dashboard', 'view_reports'])
            ->pluck('id');

        if (! $roleId) {
            return;
        }

        foreach ($permissionIds as $permissionId) {
            DB::table('permission_role')->updateOrInsert([
                'role_id' => $roleId,
                'permission_id' => $permissionId,
            ], []);
        }
    }

    public function down(): void
    {
        $roleId = DB::table('roles')->where('name', 'kepsek')->value('id');

        if (! $roleId) {
            return;
        }

        DB::table('permission_role')->where('role_id', $roleId)->delete();
        DB::table('roles')->where('id', $roleId)->delete();
    }
};
