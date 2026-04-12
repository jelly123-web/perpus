<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table): void {
            $table->string('place_of_publication')->nullable()->after('publisher');
            $table->string('edition')->nullable()->after('place_of_publication');
            $table->unsignedInteger('page_count')->nullable()->after('published_year');
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table): void {
            $table->dropColumn(['place_of_publication', 'edition', 'page_count']);
        });
    }
};
