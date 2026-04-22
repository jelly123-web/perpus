<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var list<string> */
    private array $tables = [
        'users',
        'roles',
        'permissions',
        'categories',
        'books',
        'loans',
        'settings',
        'activity_logs',
        'backups',
        'sanctions',
        'book_procurements',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
                if (! Schema::hasColumn($tableName, 'delete')) {
                    $table->boolean('delete')->default(false)->after('updated_at');
                }

                if (! Schema::hasColumn($tableName, 'deleted_at')) {
                    $table->softDeletes()->after('delete');
                }

                if (! Schema::hasColumn($tableName, 'deleted_by')) {
                    $table->foreignId('deleted_by')
                        ->nullable()
                        ->after('deleted_at')
                        ->constrained('users')
                        ->nullOnDelete();
                }

                if (! Schema::hasColumn($tableName, 'deleted_ip')) {
                    $table->string('deleted_ip', 45)->nullable()->after('deleted_by');
                }
            });
        }
    }

    public function down(): void
    {
        foreach (array_reverse($this->tables) as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
                if (Schema::hasColumn($tableName, 'deleted_by')) {
                    $table->dropForeign(['deleted_by']);
                }

                $columns = collect(['delete', 'deleted_at', 'deleted_by', 'deleted_ip'])
                    ->filter(fn (string $column) => Schema::hasColumn($tableName, $column))
                    ->all();

                if ($columns !== []) {
                    $table->dropColumn($columns);
                }
            });
        }
    }
};
