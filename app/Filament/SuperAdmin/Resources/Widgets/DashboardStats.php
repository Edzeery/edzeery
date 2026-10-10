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
        $storeCounts = storeStatusCounts();

        return [
            Stat::make(
                'ACTIVED Stores',
                $storeCounts[StoreStatusEnum::ACTIVE->value] ?? 0
            )
                ->color('success')
                ->icon(StoreStatusEnum::ACTIVE->filamentIcon()),
            Stat::make(
                'PENDING Stores',
                $storeCounts[StoreStatusEnum::PENDING->value] ?? 0
            )
                ->color('warning')
                ->icon(StoreStatusEnum::PENDING->filamentIcon()),
            Stat::make(
                'CLOSED Stores',
                $storeCounts[StoreStatusEnum::CLOSED->value] ?? 0
            )
                ->color('danger')
                ->icon(StoreStatusEnum::CLOSED->filamentIcon()),
            Stat::make(
                'All Stores',
                array_sum($storeCounts)
            )
                ->icon('heroicon-o-shopping-bag'),

            Stat::make(
                'Total Products',
                Product::all()->count()
            ),
        ];
    }
}
