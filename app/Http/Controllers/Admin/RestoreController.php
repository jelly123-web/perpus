<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\HandlesAsyncRequests;
use App\Models\ActivityLog;
use App\Models\Backup;
use App\Models\Book;
use App\Models\BookProcurement;
use App\Models\Category;
use App\Models\Loan;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Sanction;
use App\Models\Setting;
use App\Models\User;
use App\Support\ActivityLogger;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class RestoreController extends Controller
{
    use HandlesAsyncRequests;

    /** @return View */
    public function index(Request $request): View
    {
        $this->ensureSuperAdmin();

        $groups = collect($this->restorableModels())
            ->map(function (array $config, string $key): array {
                $items = $config['model']::query()
                    ->onlyTrashed()
                    ->with($this->restoreRelations($config['model']))
                    ->latest('deleted_at')
                    ->get()
                    ->map(fn (Model $model): array => [
                        'id' => $model->getKey(),
                        'table' => $key,
                        'label' => $config['label'],
                        'name' => $this->displayName($model),
                        'deleted_at' => $model->deleted_at,
                        'deleted_by' => $model->deletedBy?->name ?? 'Sistem / Tidak diketahui',
                        'deleted_ip' => $model->deleted_ip ?? '-',
                        'deleted_day' => optional($model->deleted_at)->translatedFormat('l') ?? '-',
                        'deleted_date' => optional($model->deleted_at)->translatedFormat('d M Y') ?? '-',
                        'deleted_time' => optional($model->deleted_at)->translatedFormat('H:i:s') ?? '-',
                    ]);

                return [
                    'key' => $key,
                    'label' => $config['label'],
                    'items' => $items,
                    'count' => $items->count(),
                ];
            })
            ->filter(fn (array $group): bool => $group['count'] > 0)
            ->values();

        $deletedTotal = $groups->sum('count');

        return view('admin.restore.index', compact('groups', 'deletedTotal'));
    }

    public function restore(Request $request, string $table, int $id): JsonResponse|RedirectResponse
    {
        $this->ensureSuperAdmin();

        $config = $this->restorableModels()[$table] ?? null;
        abort_if($config === null, 404);

        /** @var Model $model */
        $model = $config['model']::query()->onlyTrashed()->findOrFail($id);
        $name = $this->displayName($model);

        $model->restore();

        ActivityLogger::log('restore', 'restore', 'Mengembalikan data '.$config['label'].' '.$name, [
            'table' => $table,
            'record_id' => $id,
        ]);

        return $this->successResponse($request, 'Data berhasil dikembalikan.');
    }

    public function forceDelete(Request $request, string $table, int $id): JsonResponse|RedirectResponse
    {
        $this->ensureSuperAdmin();

        $config = $this->restorableModels()[$table] ?? null;
        abort_if($config === null, 404);

        /** @var Model $model */
        $model = $config['model']::query()->onlyTrashed()->findOrFail($id);
        $name = $this->displayName($model);

        if ($model instanceof User && $model->isSuperAdmin()) {
            return $this->errorResponse($request, 'Super admin utama tidak boleh dihapus permanen.', 422, 'user');
        }

        $this->cleanupPermanentDeleteFiles($model);
        $model->forceDelete();

        ActivityLogger::log('restore', 'force_delete', 'Menghapus permanen data '.$config['label'].' '.$name, [
            'table' => $table,
            'record_id' => $id,
        ]);

        return $this->successResponse($request, 'Data berhasil dihapus permanen.');
    }

    /**
     * @return array<string, array{label: string, model: class-string<Model>}>
     */
    private function restorableModels(): array
    {
        return [
            'users' => ['label' => 'Pengguna', 'model' => User::class],
            'roles' => ['label' => 'Role', 'model' => Role::class],
            'permissions' => ['label' => 'Permission', 'model' => Permission::class],
            'categories' => ['label' => 'Kategori', 'model' => Category::class],
            'books' => ['label' => 'Buku', 'model' => Book::class],
            'loans' => ['label' => 'Peminjaman', 'model' => Loan::class],
            'settings' => ['label' => 'Setting', 'model' => Setting::class],
            'activity_logs' => ['label' => 'Log Aktivitas', 'model' => ActivityLog::class],
            'backups' => ['label' => 'Backup', 'model' => Backup::class],
            'sanctions' => ['label' => 'Sanksi', 'model' => Sanction::class],
            'book_procurements' => ['label' => 'Pengadaan Buku', 'model' => BookProcurement::class],
        ];
    }

    /** @return list<string> */
    private function restoreRelations(string $modelClass): array
    {
        return method_exists($modelClass, 'deletedBy') ? ['deletedBy'] : [];
    }

    private function displayName(Model $model): string
    {
        foreach (['name', 'title', 'label', 'key', 'file_name', 'description', 'type'] as $field) {
            if (filled($model->{$field} ?? null)) {
                return (string) $model->{$field};
            }
        }

        if ($model instanceof Loan) {
            return 'Peminjaman #'.$model->getKey();
        }

        return class_basename($model).' #'.$model->getKey();
    }

    private function cleanupPermanentDeleteFiles(Model $model): void
    {
        if ($model instanceof Book && filled($model->cover_image)) {
            Storage::disk('public')->delete($model->cover_image);
        }

        if ($model instanceof Backup && filled($model->file_path)) {
            Storage::disk('local')->delete($model->file_path);
        }

        if ($model instanceof User && filled($model->profile_photo)) {
            Storage::disk('public')->delete($model->profile_photo);
        }
    }

    /**
     * @throws AuthorizationException
     */
    private function ensureSuperAdmin(): void
    {
        throw_unless(auth()->user()?->isSuperAdmin(), AuthorizationException::class, 'Fitur ini hanya untuk superadmin.');
    }
}
