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
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    use HandlesAsyncRequests;

    public function index(): View
    {
        $this->ensureAdminOrSuperAdmin();

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
        $this->ensureAdminOrSuperAdmin();

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

    public function import(Request $request): JsonResponse|RedirectResponse
    {
        $this->ensureAdminOrSuperAdmin();

        $data = $request->validate([
            'import_file' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $handle = fopen($data['import_file']->getRealPath(), 'r');

        if ($handle === false) {
            return $this->errorResponse($request, 'File import tidak bisa dibaca.', 422, 'import_file');
        }

        $headers = fgetcsv($handle) ?: [];
        $normalizedHeaders = collect($headers)
            ->map(fn ($header) => Str::lower(trim((string) $header)))
            ->values()
            ->all();

        $imported = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $rowData = $this->mapCsvRow($normalizedHeaders, $row);
            $name = trim((string) ($rowData['name'] ?? ''));
            $username = trim((string) ($rowData['username'] ?? ''));
            $email = trim((string) ($rowData['email'] ?? ''));

            if ($name === '' || $username === '' || $email === '') {
                continue;
            }

            $role = $this->resolveImportRole($rowData['role'] ?? null);
            if (! $role) {
                continue;
            }

            $user = User::query()
                ->where('username', $username)
                ->orWhere('email', $email)
                ->first();

            $payload = [
                'name' => $name,
                'username' => $username,
                'email' => $email,
                'phone' => $this->nullableText($rowData['phone'] ?? null),
                'kelas' => $this->nullableText($rowData['kelas'] ?? null),
                'jurusan' => $this->nullableText($rowData['jurusan'] ?? null),
                'role_id' => $role->id,
                'is_active' => ! in_array(Str::lower(trim((string) ($rowData['is_active'] ?? '1'))), ['0', 'false', 'no', 'tidak'], true),
            ];

            $password = trim((string) ($rowData['password'] ?? ''));
            if ($password !== '') {
                $payload['password'] = $password;
            } elseif (! $user) {
                $payload['password'] = $username;
            }

            if ($user) {
                if ($user->isSuperAdmin()) {
                    continue;
                }

                $user->update($payload);
            } else {
                User::query()->create($payload);
            }

            $imported++;
        }

        fclose($handle);

        ActivityLogger::log('users', 'create', 'Import data pengguna via CSV', ['imported_count' => $imported]);

        return $this->successResponse($request, $imported > 0 ? "Import pengguna berhasil. {$imported} data diproses." : 'Import selesai, tidak ada data pengguna yang valid.');
    }

    public function export(): StreamedResponse
    {
        $this->ensureAdminOrSuperAdmin();

        $fileName = 'backup-users-'.now()->format('Ymd-His').'.csv';
        $columns = ['name', 'username', 'email', 'phone', 'kelas', 'jurusan', 'role', 'is_active'];

        return response()->streamDownload(function () use ($columns): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $columns);

            User::query()
                ->with('role')
                ->whereDoesntHave('role', fn ($query) => $query->where('name', 'super_admin'))
                ->orderBy('name')
                ->chunk(200, function ($users) use ($handle): void {
                    foreach ($users as $user) {
                        fputcsv($handle, [
                            $user->name,
                            $user->username,
                            $user->email,
                            $user->phone,
                            $user->kelas,
                            $user->jurusan,
                            $user->role?->name,
                            $user->is_active ? 1 : 0,
                        ]);
                    }
                });

            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv']);
    }

    public function update(Request $request, User $user): JsonResponse|RedirectResponse
    {
        $this->ensureAdminOrSuperAdmin();

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
        $this->ensureAdminOrSuperAdmin();

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
    private function ensureAdminOrSuperAdmin(): void
    {
        $user = auth()->user();
        throw_unless($user?->isSuperAdmin() || $user?->role?->name === 'admin', AuthorizationException::class, 'Fitur ini hanya untuk admin atau superadmin.');
    }

    private function nullableText(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function resolveImportRole(mixed $roleValue): ?Role
    {
        $roleName = Str::lower(trim((string) $roleValue));

        if ($roleName === '' || $roleName === 'super_admin') {
            return null;
        }

        return Role::query()
            ->whereRaw('LOWER(name) = ?', [$roleName])
            ->orWhereRaw('LOWER(label) = ?', [$roleName])
            ->first();
    }

    /**
     * @param  list<string>  $headers
     * @param  list<string|null>  $row
     * @return array<string, mixed>
     */
    private function mapCsvRow(array $headers, array $row): array
    {
        $mapped = [];

        foreach ($headers as $index => $header) {
            if ($header === '') {
                continue;
            }

            $mapped[$header] = $row[$index] ?? null;
        }

        return $mapped;
    }
}
