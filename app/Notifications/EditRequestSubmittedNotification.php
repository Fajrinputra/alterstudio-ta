<?php

namespace App\Notifications;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Email ke editor saat client finalize pilihan foto.
 */
class EditRequestSubmittedNotification extends Notification implements ShouldQueue
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
        // Ambil context project agar isi email informatif.
        $project = Project::with(['booking.package', 'booking.client'])->find($this->projectId);
        $booking = $project?->booking;
        $packageName = $booking?->package?->name ?? 'Paket';
        $clientName = $booking?->client?->name ?? 'Klien';

        return (new MailMessage)
            ->subject('[Alter Studio] Permintaan edit baru dari klien')
            ->greeting('Halo '.$notifiable->name.',')
            ->line('Ada permintaan edit baru dari klien.')
            ->line('Klien: '.$clientName)
            ->line('Paket: '.$packageName)
            ->action('Buka Detail Project', route('projects.show', $this->projectId))
            ->line('Silakan buka detail project untuk memulai proses edit.');
    }
}
