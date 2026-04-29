<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $permissions = collect([
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
        ])->map(fn (string $label, string $name) => Permission::query()->updateOrCreate(['name' => $name], ['label' => $label]));

        $superAdminRole = Role::query()->updateOrCreate(['name' => 'super_admin'], ['label' => 'Super Admin']);
        $adminRole = Role::query()->updateOrCreate(['name' => 'admin'], ['label' => 'Admin']);
        $petugasRole = Role::query()->updateOrCreate(['name' => 'petugas'], ['label' => 'Petugas']);
        $kepsekRole = Role::query()->updateOrCreate(['name' => 'kepsek'], ['label' => 'Kepala Sekolah']);
        $guruRole = Role::query()->updateOrCreate(['name' => 'guru'], ['label' => 'Guru']);
        $siswaRole = Role::query()->updateOrCreate(['name' => 'siswa'], ['label' => 'Siswa']);

        $superAdminRole->permissions()->sync($permissions->reject(fn ($permission) => $permission->name === 'view_borrower_history')->pluck('id'));
        $adminRole->permissions()->sync($permissions->whereIn('name', ['access_dashboard', 'view_reports', 'manage_loans', 'manage_users', 'manage_roles', 'manage_categories', 'manage_books', 'manage_backups'])->pluck('id'));
        $petugasRole->permissions()->sync($permissions->whereIn('name', ['access_dashboard', 'view_reports', 'manage_loans', 'manage_categories', 'manage_books'])->pluck('id'));
        $kepsekRole->permissions()->sync($permissions->whereIn('name', ['access_dashboard', 'view_reports'])->pluck('id'));
        $guruRole->permissions()->sync($permissions->whereIn('name', ['access_dashboard', 'view_borrower_history'])->pluck('id'));
        $siswaRole->permissions()->sync($permissions->whereIn('name', ['access_dashboard', 'view_borrower_history'])->pluck('id'));

        $superAdmin = User::query()->updateOrCreate(
            ['username' => 'superadmin'],
            [
                'email' => 'superadmin@example.com',
                'name' => 'Super Admin',
                'phone' => '081234567890',
                'role_id' => $superAdminRole->id,
                'is_active' => true,
                'password' => 'superadmin',
            ]
        );

        User::query()->updateOrCreate(
            ['username' => 'siswa'],
            [
                'email' => 'siswa@example.com',
                'name' => 'Siswa Peminjam',
                'phone' => '08987654321',
                'role_id' => $siswaRole->id,
                'is_active' => true,
                'password' => 'siswa123',
            ]
        );

        // User::query()->whereIn('username', ['petugas', 'guru', 'admin'])->delete();

        collect([
            ['key' => 'library_name', 'label' => 'Nama Perpustakaan', 'type' => 'text', 'value' => 'Perpustakaan Digital Sekolah'],
            ['key' => 'max_loan_days', 'label' => 'Maksimal Hari Peminjaman', 'type' => 'text', 'value' => '1'],
            ['key' => 'late_fee_per_day', 'label' => 'Denda Terlambat per Hari', 'type' => 'text', 'value' => '1000'],
            ['key' => 'school_address', 'label' => 'Alamat Sekolah', 'type' => 'textarea', 'value' => 'Jl. Pendidikan No. 1'],
        ])->each(fn (array $setting) => Setting::query()->updateOrCreate(['key' => $setting['key']], $setting));
    }
}
