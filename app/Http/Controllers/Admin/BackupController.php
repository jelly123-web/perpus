<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\HandlesAsyncRequests;
use App\Models\Backup;
use App\Support\ActivityLogger;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BackupController extends Controller
{
    use HandlesAsyncRequests;

    /** @var array<string, list<string>> */
    private array $tableColumnsCache = [];

    public function index(): View
    {
        $backups = Backup::query()->with('creator')->latest()->paginate(10);

        return view('admin.backups.index', compact('backups'));
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $snapshot = $this->buildSnapshot();

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

    public function restore(Request $request, Backup $backup): RedirectResponse|JsonResponse
    {
        $this->ensureAdminOrSuperAdmin();

        if (! filled($backup->file_path) || ! Storage::disk('local')->exists($backup->file_path)) {
            return $this->errorResponse($request, 'File backup tidak ditemukan di storage.', 404, 'backup');
        }

        $payload = json_decode((string) Storage::disk('local')->get($backup->file_path), true);

        if (! is_array($payload)) {
            return $this->errorResponse($request, 'Format file backup tidak valid.', 422, 'backup');
        }

        $restoredCounts = [];

        DB::transaction(function () use ($payload, &$restoredCounts): void {
            foreach ($this->restoreMap() as $table => $config) {
                $rows = collect($payload[$table] ?? []);

                if ($rows->isEmpty()) {
                    continue;
                }

                $count = 0;

                $rows->each(function ($row) use ($table, $config, &$count): void {
                    if (! is_array($row)) {
                        return;
                    }

                    $attributes = $this->restoreAttributes($table, $row, $config['match']);
                    $payload = $this->filterRestorableRow($table, $row);

                    if ($attributes === [] || $payload === []) {
                        return;
                    }

                    try {
                        DB::table($table)->updateOrInsert($attributes, $payload);
                        $count++;
                    } catch (\Illuminate\Database\QueryException $e) {
                        // Handle duplicate entry error (SQLSTATE 23000, Error Code 1062)
                        if ($e->getCode() === '23000' || str_contains($e->getMessage(), '1062')) {
                            if ($table === 'books' && isset($payload['isbn']) && filled($payload['isbn'])) {
                                DB::table($table)->where('isbn', $payload['isbn'])->update($payload);
                                $count++;
                                return;
                            }

                            if ($table === 'users' && isset($payload['email']) && filled($payload['email'])) {
                                DB::table($table)->where('email', $payload['email'])->update($payload);
                                $count++;
                                return;
                            }

                            if ($table === 'users' && isset($payload['username']) && filled($payload['username'])) {
                                DB::table($table)->where('username', $payload['username'])->update($payload);
                                $count++;
                                return;
                            }
                        }

                        throw $e;
                    }
                });

                if ($count > 0) {
                    $restoredCounts[$table] = $count;
                }
            }
        });

        ActivityLogger::log('backups', 'restore', 'Melakukan restore backup '.$backup->file_name.' dengan mode update', [
            'backup_id' => $backup->id,
            'restored_tables' => $restoredCounts,
        ]);

        return $this->successResponse(
            $request,
            'Restore selesai. Data dari backup digabung dengan mode update, bukan replace.'
        );
    }

    public function download(Backup $backup): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $this->ensureAdminOrSuperAdmin();

        if (! filled($backup->file_path) || ! Storage::disk('local')->exists($backup->file_path)) {
            abort(404, 'File backup tidak ditemukan.');
        }

        $payload = json_decode((string) Storage::disk('local')->get($backup->file_path), true);

        if (! is_array($payload)) {
            abort(422, 'Format file backup tidak valid.');
        }

        $downloadName = (string) str($backup->file_name)->replaceEnd('.json', '.sql');
        $sqlDump = $this->buildSqlDump($payload, $backup->file_name);

        return response()->streamDownload(
            static function () use ($sqlDump): void {
                echo $sqlDump;
            },
            $downloadName,
            ['Content-Type' => 'application/sql; charset=UTF-8']
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function buildSqlDump(array $payload, string $sourceFileName): string
    {
        $lines = [
            '-- Perpus database backup export',
            '-- Source snapshot: '.$sourceFileName,
            '-- Generated at: '.now()->toDateTimeString(),
            'SET FOREIGN_KEY_CHECKS=0;',
            '',
        ];

        foreach (array_keys($this->restoreMap()) as $table) {
            $rows = $payload[$table] ?? null;

            if (! is_array($rows) || $rows === []) {
                continue;
            }

            $lines[] = '-- Table: '.$table;

            foreach ($rows as $row) {
                if (! is_array($row)) {
                    continue;
                }

                $filteredRow = $this->filterRestorableRow($table, $row);

                if ($filteredRow === []) {
                    continue;
                }

                $columns = array_keys($filteredRow);
                $columnList = implode(', ', array_map(fn (string $column): string => '`'.$column.'`', $columns));
                $valueList = implode(', ', array_map(fn (mixed $value): string => $this->toSqlLiteral($value), array_values($filteredRow)));

                $lines[] = sprintf('INSERT INTO `%s` (%s) VALUES (%s);', $table, $columnList, $valueList);
            }

            $lines[] = '';
        }

        $lines[] = 'SET FOREIGN_KEY_CHECKS=1;';

        return implode(PHP_EOL, $lines).PHP_EOL;
    }

    private function toSqlLiteral(mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return DB::getPdo()->quote((string) $value);
    }

    private function buildSnapshot(): array
    {
        return [
            'users' => DB::table('users')->get(),
            'roles' => DB::table('roles')->get(),
            'permissions' => DB::table('permissions')->get(),
            'permission_role' => DB::table('permission_role')->get(),
            'categories' => DB::table('categories')->get(),
            'books' => DB::table('books')->get(),
            'loans' => DB::table('loans')->get(),
            'sanctions' => DB::table('sanctions')->get(),
            'book_procurements' => DB::table('book_procurements')->get(),
            'settings' => DB::table('settings')->get(),
            'backups' => DB::table('backups')->get(),
            'activity_logs' => DB::table('activity_logs')->latest('id')->limit(100)->get(),
            'generated_at' => now()->toDateTimeString(),
        ];
    }

    /**
     * @return array<string, array{match: list<string>}>
     */
    private function restoreMap(): array
    {
        return [
            'roles' => ['match' => ['name']],
            'permissions' => ['match' => ['name']],
            'permission_role' => ['match' => ['role_id', 'permission_id']],
            'categories' => ['match' => ['slug']],
            'settings' => ['match' => ['key']],
            'users' => ['match' => ['id']],
            'books' => ['match' => ['id']],
            'loans' => ['match' => ['id']],
            'sanctions' => ['match' => ['id']],
            'book_procurements' => ['match' => ['id']],
            'backups' => ['match' => ['id']],
            'activity_logs' => ['match' => ['id']],
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  list<string>  $matchColumns
     * @return array<string, mixed>
     */
    private function restoreAttributes(string $table, array $row, array $matchColumns): array
    {
        $attributes = [];
        $availableColumns = $this->tableColumns($table);

        foreach ($matchColumns as $column) {
            if (! array_key_exists($column, $row) || ! in_array($column, $availableColumns, true)) {
                return [];
            }

            $attributes[$column] = $row[$column];
        }

        return $attributes;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function filterRestorableRow(string $table, array $row): array
    {
        $allowedColumns = $this->tableColumns($table);

        return collect($row)
            ->only($allowedColumns)
            ->all();
    }

    /**
     * @return list<string>
     */
    private function tableColumns(string $table): array
    {
        if (! array_key_exists($table, $this->tableColumnsCache)) {
            $this->tableColumnsCache[$table] = Schema::hasTable($table)
                ? Schema::getColumnListing($table)
                : [];
        }

        return $this->tableColumnsCache[$table];
    }

    /**
     * @throws AuthorizationException
     */
    private function ensureAdminOrSuperAdmin(): void
    {
        $user = auth()->user();
        throw_unless($user?->isSuperAdmin() || $user?->role?->name === 'admin', AuthorizationException::class, 'Fitur ini hanya untuk admin atau superadmin.');
    }
}
