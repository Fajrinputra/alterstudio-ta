<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menambahkan galeri multi foto untuk lokasi studio.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('studio_locations', function (Blueprint $table) {
            if (!Schema::hasColumn('studio_locations', 'photo_gallery')) {
                $table->json('photo_gallery')->nullable()->after('photo_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('studio_locations', function (Blueprint $table) {
            $table->dropColumn('photo_gallery');
        });
    }
};
