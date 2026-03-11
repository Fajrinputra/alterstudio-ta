<?php

namespace App\Notifications;

use App\Models\Schedule;
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

    public function __construct(protected int $scheduleId)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $schedule = Schedule::with(['project.booking.package'])->find($this->scheduleId);
        $booking = $schedule?->project?->booking;
        $packageName = $booking?->package?->name ?? 'Paket';
        $start = optional($schedule?->start_at)->format('d M Y H:i');
        $end = optional($schedule?->end_at)->format('d M Y H:i');

        return (new MailMessage)
            ->subject('[Alter Studio] Jadwal tugas baru')
            ->greeting('Halo '.$notifiable->name.',')
            ->line('Anda mendapatkan penugasan baru.')
            ->line('Paket: '.$packageName)
            ->line('Jadwal: '.($start ?: '-').' s/d '.($end ?: '-'))
            ->line('Lokasi: '.($schedule?->location ?? '-'))
            ->action('Lihat Jadwal', url('/admin/schedules'))
            ->line('Terima kasih.');
    }
}
