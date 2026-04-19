<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\HandlesAsyncRequests;
use App\Models\Permission;
use App\Models\Role;
use App\Support\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoleController extends Controller
{
    use HandlesAsyncRequests;

    public function index(): View
    {
        $roles = Role::query()
            ->with(['permissions', 'users'])
            ->orderByRaw("CASE name
                WHEN 'super_admin' THEN 1
                WHEN 'admin' THEN 2
                WHEN 'petugas' THEN 3
                WHEN 'kepsek' THEN 4
                WHEN 'guru' THEN 5
                WHEN 'siswa' THEN 6
                ELSE 7
            END")
            ->orderBy('label')
            ->get();
        $permissions = Permission::query()
            ->orderByRaw("CASE name
                WHEN 'access_dashboard' THEN 1
                WHEN 'view_reports' THEN 2
                WHEN 'manage_loans' THEN 3
                WHEN 'view_borrower_history' THEN 4
                WHEN 'manage_users' THEN 5
                WHEN 'manage_roles' THEN 6
                WHEN 'manage_categories' THEN 7
                WHEN 'manage_books' THEN 8
                WHEN 'manage_backups' THEN 9
                WHEN 'manage_settings' THEN 10
                ELSE 11
            END")
            ->orderBy('label')
            ->get();

        return view('admin.roles.index', compact('roles', 'permissions'));
    }

    public function updateMatrix(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'permissions' => ['array'],
            'permissions.*' => ['array'],
            'permissions.*.*' => ['exists:permissions,id'],
        ]);

        $selectedPermissions = collect($data['permissions'] ?? []);

        $roles = Role::query()->get();

        foreach ($roles as $role) {
            $role->permissions()->sync($selectedPermissions->get((string) $role->id, []));
        }

        ActivityLogger::log('roles', 'update', 'Memperbarui matrix hak akses role', [
            'role_count' => $roles->count(),
        ]);

        return $this->successResponse($request, 'Table access berhasil diperbarui.');
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'label' => ['required', 'string', 'max:255'],
            'permissions' => ['array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $role = Role::query()->create([
            'name' => $data['name'],
            'label' => $data['label'],
        ]);

        $role->permissions()->sync($data['permissions'] ?? []);
        ActivityLogger::log('roles', 'create', 'Menambahkan role '.$role->label, ['role_id' => $role->id]);

        return $this->successResponse($request, 'Role berhasil ditambahkan.');
    }

    public function update(Request $request, Role $role): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'permissions' => ['array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $role->update(['label' => $data['label']]);
        $role->permissions()->sync($data['permissions'] ?? []);

        ActivityLogger::log('roles', 'update', 'Mengubah hak akses role '.$role->label, ['role_id' => $role->id]);

        return $this->successResponse($request, 'Role berhasil diperbarui.');
    }
}
