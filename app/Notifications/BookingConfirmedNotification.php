<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingConfirmedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected Booking $booking)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('[Alter Studio] Pemesanan #'.$this->booking->id.' dikonfirmasi')
            ->greeting('Halo '.$notifiable->name.',')
            ->line('Pemesanan Anda sudah dikonfirmasi oleh admin dan siap dilanjutkan ke pembayaran.')
            ->line('Batas waktu pembayaran adalah 30 menit sejak konfirmasi ini dibuat.')
            ->action('Lanjutkan Pembayaran', route('bookings.pay', $this->booking))
            ->line('Jika pembayaran tidak diselesaikan dalam batas waktu tersebut, pemesanan akan dibatalkan otomatis.');
    }
}
