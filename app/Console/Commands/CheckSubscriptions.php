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
        $stateService = app(SubscriptionStateService::class);

        // Active subscriptions past their end date
        $subscriptions = Subscription::where('status', StatusSubscriptionEnum::ACTIVE)
            ->where(function ($q) use ($today) {
                $q->where('ends_at', '<', $today)
                    ->orWhere('trial_ends_at', '<', $today);
            })->get();

        foreach ($subscriptions as $subscription) {
            // If ends_at is past
            if ($subscription->ends_at && $subscription->ends_at->isPast()) {
                // Grant grace period once
                if (! $subscription->grace_ends_at) {
                    $subscription->update([
                        'grace_ends_at' => now()->addDays(7),
                    ]);
                    continue; // Wait for grace period to expire
                }

                // Grace period expired → suspend
                if (now()->gt($subscription->grace_ends_at)) {
                    $stateService->transition($subscription, StatusSubscriptionEnum::SUSPENDED);
                    continue; // Suspension applied, stop here
                }
            }

            // If trial is past, clear trial flag
            if ($subscription->trial_ends_at && now()->gt($subscription->trial_ends_at)) {
                $subscription->update(['is_trial' => false]);
            }
        }

        // Suspended subscriptions past their grace period → expire
        $suspended = Subscription::where('status', StatusSubscriptionEnum::SUSPENDED)
            ->whereNotNull('grace_ends_at')
            ->where('grace_ends_at', '<', $today)
            ->get();

        foreach ($suspended as $subscription) {
            $stateService->transition($subscription, StatusSubscriptionEnum::EXPIRED);
        }

        $this->info('Subscriptions checked and store statuses updated.');
    }
}
