<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

/**
 * Email verifikasi akun dalam bahasa Indonesia.
 */
class VerifyEmailNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $expireMinutes = (int) Config::get('auth.verification.expire', 60);

        return (new MailMessage)
            ->subject('[Alter Studio] Verifikasi Email Akun')
            ->greeting('Halo '.$notifiable->name.',')
            ->line('Terima kasih sudah mendaftar di Alter Studio.')
            ->line('Klik tombol berikut untuk memverifikasi alamat email Anda.')
            ->action('Verifikasi Email', $this->verificationUrl($notifiable))
            ->line('Tautan verifikasi ini berlaku selama '.$expireMinutes.' menit.')
            ->line('Jika Anda tidak membuat akun Alter Studio, abaikan email ini.')
            ->salutation('Salam, Alter Studio');
    }

    protected function verificationUrl(object $notifiable): string
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes((int) Config::get('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }
}
