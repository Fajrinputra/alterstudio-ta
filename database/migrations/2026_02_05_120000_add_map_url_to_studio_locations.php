<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Guard migration untuk memastikan kolom map_url tersedia.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('studio_locations', function (Blueprint $table) {
            if (!Schema::hasColumn('studio_locations', 'map_url')) {
                $table->string('map_url')->nullable()->after('email');
            }
        });
    }

    public function down(): void
    {
        Schema::table('studio_locations', function (Blueprint $table) {
            $table->dropColumn('map_url');
        });
    }
};
