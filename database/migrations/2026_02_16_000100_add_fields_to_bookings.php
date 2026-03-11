<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menambahkan detail jadwal, lokasi studio, dan jenis pembayaran ke booking.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'booking_time')) {
                $table->time('booking_time')->nullable()->after('booking_date');
            }
            if (!Schema::hasColumn('bookings', 'studio_location_id')) {
                $table->foreignId('studio_location_id')->nullable()->after('package_id')->constrained('studio_locations')->nullOnDelete();
            }
            if (!Schema::hasColumn('bookings', 'payment_type')) {
                $table->enum('payment_type', ['DP','FULL'])->default('DP')->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'booking_time')) {
                $table->dropColumn('booking_time');
            }
            if (Schema::hasColumn('bookings', 'studio_location_id')) {
                $table->dropConstrainedForeignId('studio_location_id');
            }
            if (Schema::hasColumn('bookings', 'payment_type')) {
                $table->dropColumn('payment_type');
            }
        });
    }
};
