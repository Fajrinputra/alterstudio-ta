<?php

namespace App\Notifications;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Email ke fotografer/editor saat penjadwalan tugas dibuat/diubah.
 */
class ScheduleAssignedNotification extends Notification implements ShouldQueue
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
        $project = Project::with(['booking.package', 'booking.studioLocation', 'booking.studioRoom', 'photographer', 'editor'])->find($this->projectId);
        $booking = $project?->booking;
        $packageName = $booking?->package?->name ?? 'Paket';
        $start = optional($project?->start_at)->format('d M Y H:i');
        $end = optional($project?->end_at)->format('d M Y H:i');

        return (new MailMessage)
            ->subject('[Alter Studio] Penugasan jadwal baru')
            ->greeting('Halo '.$notifiable->name.',')
            ->line('Anda mendapatkan penugasan baru di Alter Studio.')
            ->line('Paket: '.$packageName)
            ->line('Jadwal: '.($start ?: '-').' s/d '.($end ?: '-'))
            ->line('Lokasi: '.($booking?->location ?? '-'))
            ->action('Lihat Jadwal', url('/admin/schedules'))
            ->line('Silakan cek detail jadwal untuk menindaklanjuti tugas tersebut.');
    }
}
