<?php

namespace App\Notifications;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TrialEndingNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly User $user,
        public readonly int $daysLeft,
        public readonly Carbon $trialEndsAt,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $greeting = $this->daysLeft === 1
            ? __('notifications.trial_ending_tomorrow')
            : __('notifications.trial_ending_days', ['days' => $this->daysLeft]);

        return (new MailMessage)
            ->subject(__('notifications.trial_ending_subject'))
            ->greeting($greeting)
            ->line(__('notifications.trial_ending_body', [
                'date' => $this->trialEndsAt->format('Y-m-d'),
            ]))
            ->action(__('notifications.upgrade_now'), url('/merchant/account/billing'))
            ->line(__('notifications.trial_ending_note'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'        => 'trial_ending',
            'days_left'   => $this->daysLeft,
            'ends_at'     => $this->trialEndsAt->toIso8601String(),
            'action_url'  => url('/merchant/account/billing'),
        ];
    }
}
