<?php

namespace App\Notifications;

use App\Models\Stores\Store;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AssignmentCapacityExhaustedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Store $store,
        public string $roleScope,
        public int $unassignedCount,
    ) {}

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        $team = $this->roleScope === 'track' ? 'order tracking' : 'order confirmation';

        return (new MailMessage)
            ->subject('Assignment Capacity Exhausted')
            ->level('warning')
            ->line("Your {$team} team has reached full assignment capacity.")
            ->line("Currently {$this->unassignedCount} pending item(s) cannot be auto-assigned to an available team member.");
    }

    public function toArray($notifiable): array
    {
        return [
            'role_scope' => $this->roleScope,
            'unassigned_count' => $this->unassignedCount,
            'store_id' => $this->store->id,
        ];
    }
}