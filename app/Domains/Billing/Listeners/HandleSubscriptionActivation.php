<?php

namespace App\Domains\Billing\Listeners;

use App\Domains\Billing\Events\SubscriptionActivated;
use App\Services\Stores\StoreStatusUpdater;

class HandleSubscriptionActivation
{
    public function handle(SubscriptionActivated $event): void
    {
        $subscription = $event->subscription;

        $user = $subscription->user;

        // تحديث حالة المتاجر
        StoreStatusUpdater::update($user, 'Subscription activated');

        // reset features usage (لو حاب)
        // app(FeatureUsageService::class)->resetAll($subscription);
    }
}
