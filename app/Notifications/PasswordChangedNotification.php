<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordChangedNotification extends Notification
{
    use Queueable;

    public function __construct(public string $email)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('notifications.password_changed_subject'))
            ->greeting(__('notifications.password_changed_subject'))
            ->line(__('notifications.password_changed_body', ['email' => $this->email]))
            ->line(__('auth.thanks', ['app_name' => config('app.name')]));
    }
}
