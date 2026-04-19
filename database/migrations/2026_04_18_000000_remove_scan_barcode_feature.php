<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('books') && Schema::hasColumn('books', 'barcode')) {
            Schema::table('books', function (Blueprint $table): void {
                $table->dropUnique('books_barcode_unique');
                $table->dropColumn('barcode');
            });
        }

        if (! Schema::hasTable('permissions')) {
            return;
        }

        $permissionId = DB::table('permissions')->where('name', 'scan_books')->value('id');

        if (! $permissionId) {
            return;
        }

        DB::table('permission_role')->where('permission_id', $permissionId)->delete();
        DB::table('permissions')->where('id', $permissionId)->delete();
    }

    public function down(): void
    {
        if (Schema::hasTable('books') && ! Schema::hasColumn('books', 'barcode')) {
            Schema::table('books', function (Blueprint $table): void {
                $table->string('barcode')->nullable()->unique()->after('isbn');
            });
        }

        if (! Schema::hasTable('permissions')) {
            return;
        }

        $now = now();

        DB::table('permissions')->updateOrInsert(
            ['name' => 'scan_books'],
            ['label' => 'Scan Barcode', 'created_at' => $now, 'updated_at' => $now]
        );
    }
};
