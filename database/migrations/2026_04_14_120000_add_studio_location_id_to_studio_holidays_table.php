<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('studio_holidays', function (Blueprint $table) {
            $table->foreignId('studio_location_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->cascadeOnDelete();
        });

        Schema::table('studio_holidays', function (Blueprint $table) {
            $table->dropUnique(['holiday_date']);
            $table->unique(['studio_location_id', 'holiday_date'], 'studio_holidays_location_date_unique');
        });

        if (Schema::hasTable('studio_locations')) {
            $firstLocationId = DB::table('studio_locations')->orderBy('id')->value('id');

            if ($firstLocationId) {
                DB::table('studio_holidays')
                    ->whereNull('studio_location_id')
                    ->update(['studio_location_id' => $firstLocationId]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('studio_holidays', function (Blueprint $table) {
            $table->dropUnique('studio_holidays_location_date_unique');
            $table->unique('holiday_date');
            $table->dropConstrainedForeignId('studio_location_id');
        });
    }
};
