<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Booking;

/**
 * Email notifikasi saat booking baru dibuat.
 */
class BookingCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected Booking $booking)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $booking = $this->booking;
        $packageName = $booking->package->name ?? 'Paket';
        $status = $booking->status;
        $total = number_format($booking->total_price);
        $date = optional($booking->booking_date)->format('d M Y');
        $isClientRecipient = (int) ($notifiable->id ?? 0) === (int) $booking->client_id;

        return (new MailMessage)
            ->subject("[Alter Studio] Booking #{$booking->id} - {$packageName}")
            ->markdown('mail.booking.created', [
                'booking' => $booking,
                'packageName' => $packageName,
                'status' => $status,
                'total' => $total,
                'date' => $date,
                'isClientRecipient' => $isClientRecipient,
            ]);
    }
}
