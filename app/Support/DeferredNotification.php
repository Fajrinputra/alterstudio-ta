<?php

namespace App\Support;

use Illuminate\Notifications\Notification as BaseNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * Mengirim notifikasi setelah response selesai agar proses simpan data terasa cepat.
 */
class DeferredNotification
{
    public static function to(mixed $notifiable, BaseNotification $notification): void
    {
        self::send(collect([$notifiable]), $notification);
    }

    public static function send(iterable $notifiables, BaseNotification $notification): void
    {
        $recipients = collect($notifiables)
            ->filter()
            ->unique(fn ($recipient) => method_exists($recipient, 'getKey')
                ? $recipient::class.':'.$recipient->getKey()
                : spl_object_id($recipient))
            ->values();

        if ($recipients->isEmpty()) {
            return;
        }

        if (app()->runningUnitTests()) {
            Notification::send($recipients, $notification);

            return;
        }

        app()->terminating(function () use ($recipients, $notification): void {
            try {
                Notification::send($recipients, $notification);
            } catch (\Throwable $exception) {
                Log::error('Gagal mengirim notifikasi setelah response selesai.', [
                    'notification' => $notification::class,
                    'message' => $exception->getMessage(),
                ]);
            }
        });
    }
}
