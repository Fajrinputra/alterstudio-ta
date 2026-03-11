<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Email konfirmasi saat pembayaran tervalidasi.
 */
class PaymentConfirmedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected int $paymentId)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        // Referensi payment ID dipakai agar payload queue tetap ringan.
        $payment = Payment::with(['booking.package'])->find($this->paymentId);
        $booking = $payment?->booking;

        return (new MailMessage)
            ->subject('[Alter Studio] Pembayaran terkonfirmasi')
            ->greeting('Halo '.$notifiable->name.',')
            ->line('Pembayaran Anda telah tervalidasi oleh sistem.')
            ->line('No. Pemesanan: #'.($booking?->id ?? '-'))
            ->line('Paket: '.($booking?->package?->name ?? 'Paket'))
            ->line('Jenis pembayaran: '.(($payment?->type ?? 'FULL') === 'DP' ? 'DP' : 'Pelunasan'))
            ->line('Nominal: Rp '.number_format((int) ($payment?->amount ?? 0), 0, ',', '.'))
            ->action('Lihat Pemesanan', route('bookings.index'))
            ->line('Terima kasih.');
    }
}
