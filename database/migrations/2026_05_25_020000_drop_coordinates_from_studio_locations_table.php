<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('studio_locations', function (Blueprint $table) {
            if (Schema::hasColumn('studio_locations', 'latitude')) {
                $table->dropColumn('latitude');
            }

            if (Schema::hasColumn('studio_locations', 'longitude')) {
                $table->dropColumn('longitude');
            }
        });
    }

    public function down(): void
    {
        Schema::table('studio_locations', function (Blueprint $table) {
            if (! Schema::hasColumn('studio_locations', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('map_url');
            }

            if (! Schema::hasColumn('studio_locations', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }
        });
    }
};
