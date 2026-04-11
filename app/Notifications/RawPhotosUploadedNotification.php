<?php

namespace App\Notifications;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Email ke client saat fotografer mengunggah foto RAW.
 */
class RawPhotosUploadedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected int $projectId)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $project = Project::with(['booking.package'])->find($this->projectId);
        $booking = $project?->booking;
        $packageName = $booking?->package?->name ?? 'Paket';

        return (new MailMessage)
            ->subject('[Alter Studio] Foto RAW telah diunggah')
            ->greeting('Halo '.$notifiable->name.',')
            ->line('Foto untuk sesi Anda sudah diunggah ke sistem.')
            ->line('Paket: '.$packageName)
            ->line('Silakan buka halaman pemesanan untuk melihat foto dan memilih maksimal 5 foto yang ingin diedit.')
            ->action('Lihat Pemesanan', route('bookings.index'))
            ->line('Terima kasih telah menggunakan layanan Alter Studio.');
    }
}
