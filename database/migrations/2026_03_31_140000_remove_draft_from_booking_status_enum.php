<?php

use App\Models\Booking;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::table('bookings')
            ->where('status', 'DRAFT')
            ->update(['status' => Booking::STATUS_WAITING_PAYMENT]);

        DB::statement("
            ALTER TABLE bookings
            MODIFY status ENUM('WAITING_PAYMENT','DP_PAID','PAID','CANCELLED')
            NOT NULL DEFAULT 'WAITING_PAYMENT'
        ");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("
            ALTER TABLE bookings
            MODIFY status ENUM('DRAFT','WAITING_PAYMENT','DP_PAID','PAID','CANCELLED')
            NOT NULL DEFAULT 'WAITING_PAYMENT'
        ");
    }
};
