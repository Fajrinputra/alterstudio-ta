<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menambahkan deskripsi naratif untuk halaman publik cabang.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('studio_locations', function (Blueprint $table) {
            if (!Schema::hasColumn('studio_locations', 'description')) {
                $table->text('description')->nullable()->after('address');
            }
        });
    }

    public function down(): void
    {
        Schema::table('studio_locations', function (Blueprint $table) {
            if (Schema::hasColumn('studio_locations', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
};
