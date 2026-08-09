<?php

namespace App\Services\Stores;

use App\Models\Stores\Store;
use App\Models\Stores\StoreStatusHistory;
use App\Enums\Store\StoreStatusEnum;
use App\Models\User;

class StoreStatusUpdater
{
    public static function update(User $user, ?string $reason = null): ?StoreStatusHistory
    {
        $subscription = $user->latestSubscription();

        if (!$subscription) {
            $status = StoreStatusEnum::PENDING;
        } elseif ($subscription->onTrial()) {
            $status = StoreStatusEnum::ACTIVE;
        } elseif ($subscription->isActive()) {
            $status = StoreStatusEnum::ACTIVE;
        } elseif ($subscription->ends_at && now()->gt($subscription->ends_at)) {
            $status = StoreStatusEnum::SUSPENDED;
        } else {
            $status = StoreStatusEnum::PENDING;
        }

        $store = $user->stores()->first();

        if (!$store) {
            return null;
        }

        return StoreStatusHistory::create([
            'store_id' => $store->id,
            'status' => $status->value,
            'reason' => $reason,
        ]);
    }
}
