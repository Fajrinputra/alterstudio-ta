<?php

namespace App\Notifications;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Email ke client saat editor mengunggah hasil final.
 */
class FinalPhotosReadyNotification extends Notification implements ShouldQueue
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
            ->subject('[Alter Studio] Foto final telah diterima')
            ->greeting('Halo '.$notifiable->name.',')
            ->line('Hasil edit final Anda sudah tersedia.')
            ->line('Paket: '.$packageName)
            ->action('Unduh Foto Final', route('bookings.index'))
            ->line('Terima kasih sudah menggunakan layanan Alter Studio.');
    }
}
