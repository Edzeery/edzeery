<?php

namespace App\Domains\Analytics\Support;

use App\Enums\Store\StorePermissionEnum;
use App\Models\Orders\Order;
use App\Models\Stores\Shipping\StoreShippingProvider;
use App\Models\Stores\Team\StoreMembership;
use Illuminate\Support\Collection;

final class DashboardFilterOptions
{
    public function carriers(string $storeId): Collection
    {
        $providerIds = Order::query()
            ->where('store_id', $storeId)
            ->whereNotNull('shipping_provider_id')
            ->pluck('shipping_provider_id')
            ->unique();

        return StoreShippingProvider::query()
            ->where('store_id', $storeId)
            ->whereIn('id', $providerIds)
            ->orderBy('name')
            ->get(['id', 'name', 'is_active']);
    }

    public function members(?StoreMembership $current): Collection
    {
        if (! $current || ! $current->is_active) {
            return collect();
        }

        $storeId = $current->store_id;

        $hasTeamView = $current->hasPermission(StorePermissionEnum::STATS_TEAM_VIEW->value)
            || $current->hasPermission(StorePermissionEnum::TEAM_VIEW->value)
            || $current->hasAnyPermission([StorePermissionEnum::OWNER->value, StorePermissionEnum::ADMIN->value]);

        $hasTeamViewOwn = $current->hasPermission(StorePermissionEnum::TEAM_VIEW_OWN->value)
            && ! $hasTeamView;

        $query = StoreMembership::query()
            ->with('user')
            ->where('store_id', $storeId)
            ->where('is_active', true);

        if ($hasTeamView) {
            // all
        } elseif ($hasTeamViewOwn) {
            $ids = $current->subordinates()->pluck('id')->merge([$current->id])->unique()->toArray();
            $query->whereIn('id', $ids);
        } else {
            $query->where('id', $current->id);
        }

        return $query->orderBy('id')->get()->map(function ($m) {
            return (object) [
                'id' => $m->id,
                'name' => $m->user?->name ?? $m->user?->email ?? $m->id,
            ];
        });
    }
}
