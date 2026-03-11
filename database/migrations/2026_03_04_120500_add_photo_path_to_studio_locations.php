<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menambahkan cover photo tunggal pada lokasi studio.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('studio_locations', function (Blueprint $table) {
            if (!Schema::hasColumn('studio_locations', 'photo_path')) {
                $table->string('photo_path')->nullable()->after('map_url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('studio_locations', function (Blueprint $table) {
            $table->dropColumn('photo_path');
        });
    }
};
