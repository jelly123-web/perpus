<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoginOtpNotification extends Notification
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
        $appName = \App\Models\Setting::valueOr('app_name', 'LibraVault');

        return (new MailMessage)
            ->subject('Kode OTP Login - '.$appName)
            ->greeting('Halo!')
            ->line('Gunakan kode OTP berikut untuk login ke akun Anda:')
            ->line('')
            ->line('**'.$this->token.'**')
            ->line('')
            ->line('Kode ini berlaku selama 10 menit.')
            ->line('Jika Anda tidak meminta login OTP, abaikan email ini.')
            ->salutation('Terima kasih, Tim '.$appName);
    }
}
