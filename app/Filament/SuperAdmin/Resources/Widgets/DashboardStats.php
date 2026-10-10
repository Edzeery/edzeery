<?php

namespace App\Filament\SuperAdmin\Resources\Widgets;

use App\Enums\Store\StoreStatusEnum;
use App\Models\Products\Product;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStats extends StatsOverviewWidget
{
    protected static ?int $sort = -3;

    protected static bool $isLazy = true;

    protected function getStats(): array
    {
        return [
            Stat::make(
                'ACTIVED Stores',
                AllActiveStoresCount()
            )
                ->color('success')
                ->icon(StoreStatusEnum::ACTIVE->filamentIcon()),
            Stat::make(
                'PENDING Stores',
                AllPendingStoresCount()
            )
                ->color('warning')
                ->icon(StoreStatusEnum::PENDING->filamentIcon()),
            Stat::make(
                'CLOSED Stores',
                AllClosedStoresCount()
            )
                ->color('danger')
                ->icon(StoreStatusEnum::CLOSED->filamentIcon()),
            Stat::make(
                'All Stores',
                AllStoresCount()
            )
                ->icon('heroicon-o-shopping-bag'),

            Stat::make(
                'Total Products',
                Product::all()->count()
            ),
        ];
    }
}
