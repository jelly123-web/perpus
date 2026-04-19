<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $permissions = [
            'access_dashboard' => 'Dashboard',
            'view_reports' => 'Laporan',
            'manage_loans' => 'Peminjaman Buku',
            'view_borrower_history' => 'Riwayat Peminjaman',
            'manage_users' => 'Kelola Akun Pengguna',
            'manage_roles' => 'Table Access',
            'manage_categories' => 'Kategori Buku',
            'manage_books' => 'Kelola Data Buku',
            'manage_backups' => 'Backup Data',
            'manage_settings' => 'Setting',
        ];

        foreach ($permissions as $name => $label) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $name],
                ['label' => $label, 'created_at' => $now, 'updated_at' => $now]
            );
        }

        $rolePermissionMap = [
            'super_admin' => array_keys($permissions),
            'admin' => ['access_dashboard', 'view_reports', 'manage_loans', 'manage_users', 'manage_roles', 'manage_categories', 'manage_books'],
            'petugas' => ['access_dashboard', 'view_reports', 'manage_loans', 'manage_categories', 'manage_books'],
            'kepsek' => ['access_dashboard', 'view_reports'],
            'guru' => ['access_dashboard', 'view_borrower_history'],
            'siswa' => ['access_dashboard', 'view_borrower_history'],
        ];

        $roles = DB::table('roles')->pluck('id', 'name');
        $permissionIds = DB::table('permissions')->pluck('id', 'name');

        foreach ($rolePermissionMap as $roleName => $permissionNames) {
            $roleId = $roles[$roleName] ?? null;

            if (! $roleId) {
                continue;
            }

            $resolvedIds = collect($permissionNames)
                ->map(fn (string $name) => $permissionIds[$name] ?? null)
                ->filter()
                ->values();

            DB::table('permission_role')->where('role_id', $roleId)->delete();

            foreach ($resolvedIds as $permissionId) {
                DB::table('permission_role')->insert([
                    'role_id' => $roleId,
                    'permission_id' => $permissionId,
                ]);
            }
        }
    }

    public function down(): void
    {
        $permissionIds = DB::table('permissions')
            ->whereIn('name', ['manage_categories'])
            ->pluck('id');

        if ($permissionIds->isNotEmpty()) {
            DB::table('permission_role')->whereIn('permission_id', $permissionIds)->delete();
            DB::table('permissions')->whereIn('id', $permissionIds)->delete();
        }
    }
};
