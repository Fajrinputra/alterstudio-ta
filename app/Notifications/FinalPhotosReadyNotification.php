<?php

namespace App\Notifications;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Email ke client saat editor menandai hasil final sudah tersedia di Drive.
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
            ->subject('[Alter Studio] Foto final Anda sudah siap')
            ->greeting('Halo '.$notifiable->name.',')
            ->line('Editor sudah menandai hasil edit final tersedia di folder Drive project.')
            ->line('Paket: '.$packageName)
            ->line('Link Drive hasil final berlaku selama 3 hari sejak hasil dibagikan. Silakan segera buka dan unduh file Anda.')
            ->action('Lihat Pemesanan', route('bookings.index'))
            ->line('Terima kasih sudah menggunakan layanan Alter Studio.');
    }
}
