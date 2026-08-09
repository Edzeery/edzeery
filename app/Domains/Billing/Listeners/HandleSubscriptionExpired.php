<?php

namespace App\Domains\Billing\Listeners;

use App\Services\Stores\StoreStatusUpdater;

class HandleSubscriptionExpired
{
    public function handle($event): void
    {
        $subscription = $event->subscription;

        StoreStatusUpdater::update($subscription->user, 'Subscription expired');
    }
}
