<?php

namespace App\Observers;

use App\Domains\Plan\Services\FeatureUsageService;
use App\Models\Stores\Store;

class StoreObserver
{
    public function creating(Store $store): void
    {
        if (auth()->check()) {
            $store->user_id = auth()->id();
        }
    }

    public function deleted(Store $store): void
    {
        $this->decrementQuota($store);
    }

    public function forceDeleted(Store $store): void
    {
        $this->decrementQuota($store);
    }

    private function decrementQuota(Store $store): void
    {
        $user = $store->user;

        if (! $user) {
            return;
        }

        $subscription = $user->latestSubscription();

        if ($subscription) {
            app(FeatureUsageService::class)->decrement($subscription, 'stores_max');
        }
    }
}
