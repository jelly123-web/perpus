<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\HandlesAsyncRequests;
use App\Models\Backup;
use App\Support\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BackupController extends Controller
{
    use HandlesAsyncRequests;

    public function index(): View
    {
        $backups = Backup::query()->with('creator')->latest()->paginate(10);

        return view('admin.backups.index', compact('backups'));
    }

    public function store(\Illuminate\Http\Request $request): RedirectResponse|JsonResponse
    {
        $snapshot = [
            'users' => DB::table('users')->get(),
            'roles' => DB::table('roles')->get(),
            'permissions' => DB::table('permissions')->get(),
            'categories' => DB::table('categories')->get(),
            'books' => DB::table('books')->get(),
            'loans' => DB::table('loans')->get(),
            'settings' => DB::table('settings')->get(),
            'activity_logs' => DB::table('activity_logs')->latest('id')->limit(100)->get(),
            'generated_at' => now()->toDateTimeString(),
        ];

        $fileName = 'backup-'.now()->format('Ymd-His').'.json';
        $path = 'backups/'.$fileName;
        Storage::disk('local')->put($path, json_encode($snapshot, JSON_PRETTY_PRINT));

        $backup = Backup::query()->create([
            'file_name' => $fileName,
            'file_path' => $path,
            'size_bytes' => Storage::disk('local')->size($path),
            'created_by' => auth()->id(),
        ]);

        ActivityLogger::log('backups', 'create', 'Membuat backup '.$backup->file_name, ['backup_id' => $backup->id]);

        return $this->successResponse($request, 'Backup berhasil dibuat di storage/app/backups.');
    }
}
