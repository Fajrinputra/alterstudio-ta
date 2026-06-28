<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Email reset kata sandi dalam bahasa Indonesia.
 */
class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(public string $token)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $expireMinutes = (int) config('auth.passwords.users.expire', 60);
        $resetUrl = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject('[Alter Studio] Reset Kata Sandi')
            ->greeting('Halo '.$notifiable->name.',')
            ->line('Kami menerima permintaan reset kata sandi untuk akun Alter Studio Anda.')
            ->line('Klik tombol berikut untuk membuat kata sandi baru.')
            ->action('Reset Kata Sandi', $resetUrl)
            ->line('Tautan reset ini berlaku selama '.$expireMinutes.' menit.')
            ->line('Jika Anda tidak meminta reset kata sandi, abaikan email ini.')
            ->salutation('Salam, Alter Studio');
    }
}
