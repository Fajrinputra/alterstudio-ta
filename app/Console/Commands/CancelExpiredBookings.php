<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Console\Command;

class CancelExpiredBookings extends Command
{
    protected $signature = 'bookings:cancel-expired';

    protected $description = 'Batalkan booking menunggu pembayaran yang melewati batas 30 menit';

    public function handle(): int
    {
        $expiredBookings = Booking::query()
            ->where('status', Booking::STATUS_WAITING_PAYMENT)
            ->where('created_at', '<=', now()->subMinutes(30))
            ->get();

        $count = 0;

        foreach ($expiredBookings as $booking) {
            $booking->update(['status' => Booking::STATUS_CANCELLED]);

            $booking->payments()
                ->where('status', Payment::STATUS_PENDING)
                ->update([
                    'status' => Payment::STATUS_EXPIRED,
                    'transaction_status' => 'payment_window_expired',
                    'paid_at' => null,
                ]);

            $count++;
        }

        $this->info("Expired bookings cancelled: {$count}");

        return self::SUCCESS;
    }
}
