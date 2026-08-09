<?php

namespace App\Services\Subscriptions;

use App\Models\Stores\Store;

class FeatureUsageService
{
    public function usage(Store $store, string $featureSlug): int
    {
        return match ($featureSlug) {

            // 🧱 Products
            'products_limit' =>
                $store->products()->count(),

            // 👥 Staff (بدون المالك)
            'staff_limit' =>
                $store->memberships()
                    ->where('user_id', '!=', $store->user_id)
                    ->count(),

            // 📦 Orders today
            'daily_orders_limit' =>
                $store->orders()
                    ->whereDate('created_at', today())
                    ->count(),

            // 🚚 Delivery agents
            'delivery_agents_limit' =>
                $store->memberships()
                    ->whereHas('role', fn ($q) =>
                        $q->where('key', 'delivery_agent')
                    )->count(),

            default => 0,
        };
    }
}
