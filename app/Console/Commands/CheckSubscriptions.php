<?php

namespace App\Console\Commands;

use App\Domains\Billing\Services\SubscriptionStateService;
use App\Enums\SubscriptionPayment\StatusSubscriptionEnum;
use Illuminate\Console\Command;
use App\Models\billing\Subscription;
use Carbon\Carbon;

class CheckSubscriptions extends Command
{
    protected $signature = 'subscriptions:check-expired';
    protected $description = 'Check subscriptions, update status, send notifications';

    public function handle()
    {
        $today = Carbon::today();
        $stateService = new SubscriptionStateService;

        // اشتراكات انتهت أو trial انتهت
        $subscriptions = Subscription::where(function ($q) use ($today) {
            $q->where('ends_at', '<', $today)
                ->orWhere('trial_ends_at', '<', $today);
        })->where('status', 'active')->get();
        foreach ($subscriptions as $subscription) {

            if ($subscription->ends_at->isPast()) {

                if (!$subscription->grace_ends_at) {
                    $subscription->update([
                        'grace_ends_at' => now()->addDays(7),
                    ]);
                } elseif (now()->gt($subscription->grace_ends_at)) {
                    $stateService->transition($subscription, StatusSubscriptionEnum::SUSPENDED);
                }
            }

            if ($subscription->trial_ends_at && now()->gt($subscription->trial_ends_at)) {
                $subscription->is_trial = false;
            }

            if ($subscription->ends_at && now()->gt($subscription->ends_at)) {
                $subscription->status = 'expired';
            }

            $subscription->save();

        }

        $this->info('Subscriptions checked and store statuses updated.');
    }
}
