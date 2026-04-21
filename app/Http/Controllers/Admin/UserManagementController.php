<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\HandlesAsyncRequests;
use App\Models\Role;
use App\Models\User;
use App\Support\ActivityLogger;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    use HandlesAsyncRequests;

    public function index(): View
    {
        $this->ensureSuperAdmin();

        $users = User::query()
            ->with('role')
            ->whereDoesntHave('role', fn ($query) => $query->where('name', 'super_admin'))
            ->latest()
            ->paginate(10);
        $roles = Role::query()
            ->where('name', '!=', 'super_admin')
            ->orderBy('label')
            ->get();
        $accountStats = [
            'total' => User::query()->whereDoesntHave('role', fn ($query) => $query->where('name', 'super_admin'))->count(),
            'petugas' => User::query()->whereHas('role', fn ($query) => $query->whereIn('name', ['petugas', 'admin']))->count(),
            'peminjam' => User::query()->whereHas('role', fn ($query) => $query->whereIn('name', ['siswa', 'guru']))->count(),
            'aktif' => User::query()
                ->where('is_active', true)
                ->whereDoesntHave('role', fn ($query) => $query->where('name', 'super_admin'))
                ->count(),
        ];

        return view('admin.users.index', compact('users', 'roles', 'accountStats'));
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $this->ensureSuperAdmin();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'kelas' => ['nullable', 'string', 'max:100'],
            'jurusan' => ['nullable', 'string', 'max:100'],
            'role_id' => ['required', 'exists:roles,id'],
            'password' => ['required', 'string', 'min:5'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'email.unique' => 'Email sudah dipakai akun lain.',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        $user = User::query()->create($data);
        ActivityLogger::log('users', 'create', 'Menambahkan user '.$user->name, ['user_id' => $user->id]);

        return $this->successResponse($request, 'User berhasil ditambahkan.');
    }

    public function update(Request $request, User $user): JsonResponse|RedirectResponse
    {
        $this->ensureSuperAdmin();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username,'.$user->id],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'phone' => ['nullable', 'string', 'max:50'],
            'kelas' => ['nullable', 'string', 'max:100'],
            'jurusan' => ['nullable', 'string', 'max:100'],
            'role_id' => ['required', 'exists:roles,id'],
            'password' => ['nullable', 'string', 'min:5'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'email.unique' => 'Email sudah dipakai akun lain.',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        if (! $data['password']) {
            unset($data['password']);
        }

        $user->update($data);
        ActivityLogger::log('users', 'update', 'Mengubah user '.$user->name, ['user_id' => $user->id]);

        return $this->successResponse($request, 'User berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user): JsonResponse|RedirectResponse
    {
        $this->ensureSuperAdmin();

        if ($user->isSuperAdmin()) {
            return $this->errorResponse($request, 'Super admin utama tidak boleh dihapus.', 422, 'user');
        }

        ActivityLogger::log('users', 'delete', 'Menghapus user '.$user->name, ['user_id' => $user->id]);
        $user->delete();

        return $this->successResponse($request, 'User berhasil dihapus.');
    }

    /**
     * @throws AuthorizationException
     */
    private function ensureSuperAdmin(): void
    {
        throw_unless(auth()->user()?->isSuperAdmin(), AuthorizationException::class, 'Fitur ini hanya untuk superadmin.');
    }
}
