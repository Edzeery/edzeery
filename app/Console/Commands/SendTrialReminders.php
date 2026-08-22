<?php

namespace App\Console\Commands;

use App\Enums\SubscriptionPayment\StatusSubscriptionEnum;
use App\Models\billing\Subscription;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class SendTrialReminders extends Command
{
    protected $signature = 'billing:trial-reminders';

    protected $description = 'Send trial ending reminders at day 10 and day 13';

    public function handle(): int
    {
        $today = now()->startOfDay();

        $subscriptions = Subscription::query()
            ->where('status', StatusSubscriptionEnum::PENDING)
            ->where('is_trial', true)
            ->whereNotNull('trial_ends_at')
            ->whereBetween('trial_ends_at', [$today, $today->copy()->addDays(4)])
            ->with('user')
            ->get();

        $sent = 0;

        foreach ($subscriptions as $subscription) {
            $user = $subscription->user;
            if (! $user) {
                continue;
            }

            $daysLeft = (int) $today->diffInDays($subscription->trial_ends_at, false);

            if ($daysLeft === 4 || $daysLeft === 1) {
                Notification::route('mail', $user->email)
                    ->notify(new \App\Notifications\TrialEndingNotification(
                        user: $user,
                        daysLeft: $daysLeft,
                        trialEndsAt: $subscription->trial_ends_at,
                    ));

                $sent++;
            }
        }

        $this->info("Sent {$sent} trial reminder(s).");

        return self::SUCCESS;
    }
}
