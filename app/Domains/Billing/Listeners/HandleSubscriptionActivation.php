<?php

namespace App\Domains\Billing\Listeners;

use App\Domains\Billing\Events\SubscriptionActivated;
use App\Domains\Plan\Services\FeatureUsageService;
use App\Services\Stores\StoreStatusUpdater;

class HandleSubscriptionActivation
{
    public function handle(SubscriptionActivated $event): void
    {
        $subscription = $event->subscription;

        $user = $subscription->user;

        StoreStatusUpdater::update($user, 'Subscription activated');

        app(FeatureUsageService::class)->resetAll($subscription);
    }
}
