<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('projects')) {
            return;
        }

        // Jika ada data lama ganda, sisakan project terbaru agar relasi Booking -> Project benar-benar 1:1.
        $duplicateBookingIds = DB::table('projects')
            ->select('booking_id')
            ->groupBy('booking_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('booking_id');

        foreach ($duplicateBookingIds as $bookingId) {
            $keeperId = DB::table('projects')
                ->where('booking_id', $bookingId)
                ->orderByDesc('id')
                ->value('id');

            DB::table('projects')
                ->where('booking_id', $bookingId)
                ->where('id', '!=', $keeperId)
                ->delete();
        }

        Schema::table('projects', function (Blueprint $table) {
            $table->unique('booking_id', 'projects_booking_id_unique');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('projects')) {
            return;
        }

        Schema::table('projects', function (Blueprint $table) {
            $table->dropUnique('projects_booking_id_unique');
        });
    }
};
