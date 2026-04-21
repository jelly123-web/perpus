<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomResetPasswordNotification extends Notification
{
    use Queueable;

    public $token;

    /**
     * Create a new notification instance.
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $appName = \App\Models\Setting::valueOr('app_name', 'LibraVault');
        
        return (new MailMessage)
            ->subject('Kode Verifikasi Reset Password - ' . $appName)
            ->greeting('Halo!')
            ->line('Kami menerima permintaan untuk mereset password akun Anda.')
            ->line('Silakan gunakan kode verifikasi di bawah ini untuk melanjutkan proses reset password:')
            ->line('')
            ->line('**' . $this->token . '**')
            ->line('')
            ->line('Kode ini hanya berlaku selama 60 menit.')
            ->line('Jika Anda tidak merasa melakukan permintaan ini, abaikan email ini.')
            ->salutation('Terima kasih, Tim ' . $appName);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
