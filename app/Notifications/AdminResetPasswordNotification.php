<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminResetPasswordNotification extends Notification
{
    public function __construct(private readonly string $url)
    {
    }

    /** @return string[] */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Ubah Kata Sandi Admin — WADUH')
            ->greeting('Halo, '.$notifiable->nama_admin.'!')
            ->line('Kami menerima permintaan untuk mengubah kata sandi akun admin Anda.')
            ->action('Ubah Kata Sandi', $this->url)
            ->line('Tautan ini berlaku selama 60 menit.')
            ->line('Jika Anda tidak meminta perubahan kata sandi, abaikan email ini.');
    }
}
