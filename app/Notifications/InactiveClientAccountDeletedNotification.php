<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InactiveClientAccountDeletedNotification extends Notification
{
    public function __construct(protected string $cutoffDate)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('[Alter Studio] Akun dihapus karena tidak aktif')
            ->greeting('Halo '.$notifiable->name.',')
            ->line('Akun Anda di Alter Studio dihapus otomatis karena tidak memiliki transaksi dalam 6 bulan terakhir.')
            ->line('Batas pengecekan terakhir: '.$this->cutoffDate.'.')
            ->line('Jika ingin menggunakan layanan Alter Studio kembali, silakan melakukan registrasi akun baru.');
    }
}
