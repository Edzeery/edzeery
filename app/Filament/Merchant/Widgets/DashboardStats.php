<?php

namespace App\Filament\Merchant\Widgets;

use App\Enums\Store\StorePermissionEnum;
use App\Enums\Store\StoreRoleEnum;
use App\Enums\Store\StoreStatusEnum;
use App\Models\Stores\Store;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStats extends StatsOverviewWidget
{


    protected function getStats(): array
    {
        $user = auth()->user();

        return [
            Stat::make(
                'My Stores',
                $user->stores()->count()
            )->visible(membership($user)->can(StorePermissionEnum::STORE_VIEW)),

            Stat::make(
                'My Active Stores',
                $user->stores()->where('status', StoreStatusEnum::ACTIVE)->count()
            )->visible(membership($user)->can(StorePermissionEnum::STORE_VIEW)),

            Stat::make(
                'Total Products',
                \App\Models\Products\Product::whereIn(
                    'store_id',
                    $user->stores->pluck('id')
                )->count()
            ),
        ];
    }
}
