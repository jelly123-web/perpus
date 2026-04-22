<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('permission_role')) {
            return;
        }

        Schema::table('permission_role', function (Blueprint $table): void {
            if (! Schema::hasColumn('permission_role', 'created_at')
                && ! Schema::hasColumn('permission_role', 'updated_at')) {
                $table->timestamps();

                return;
            }

            if (! Schema::hasColumn('permission_role', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }

            if (! Schema::hasColumn('permission_role', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('permission_role')) {
            return;
        }

        Schema::table('permission_role', function (Blueprint $table): void {
            $columns = collect(['created_at', 'updated_at'])
                ->filter(fn (string $column) => Schema::hasColumn('permission_role', $column))
                ->all();

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
