<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $permissions = [
            'access_dashboard' => 'Akses dashboard',
            'view_borrower_history' => 'Melihat riwayat peminjaman peminjam',
        ];

        foreach ($permissions as $name => $label) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $name],
                ['label' => $label, 'updated_at' => $now, 'created_at' => $now]
            );
        }

        $rolePermissionMap = [
            'super_admin' => ['access_dashboard'],
            'admin' => ['access_dashboard'],
            'petugas' => ['access_dashboard'],
            'guru' => ['access_dashboard', 'view_borrower_history'],
            'siswa' => ['access_dashboard', 'view_borrower_history'],
        ];

        $roles = DB::table('roles')
            ->whereIn('name', array_keys($rolePermissionMap))
            ->pluck('id', 'name');

        $permissionIds = DB::table('permissions')
            ->whereIn('name', array_keys($permissions))
            ->pluck('id', 'name');

        foreach ($rolePermissionMap as $roleName => $permissionNames) {
            $roleId = $roles[$roleName] ?? null;

            if (! $roleId) {
                continue;
            }

            foreach ($permissionNames as $permissionName) {
                $permissionId = $permissionIds[$permissionName] ?? null;

                if (! $permissionId) {
                    continue;
                }

                DB::table('permission_role')->updateOrInsert([
                    'role_id' => $roleId,
                    'permission_id' => $permissionId,
                ], []);
            }
        }
    }

    public function down(): void
    {
        $permissionIds = DB::table('permissions')
            ->whereIn('name', ['access_dashboard', 'view_borrower_history'])
            ->pluck('id');

        if ($permissionIds->isEmpty()) {
            return;
        }

        DB::table('permission_role')->whereIn('permission_id', $permissionIds)->delete();
        DB::table('permissions')->whereIn('id', $permissionIds)->delete();
    }
};
