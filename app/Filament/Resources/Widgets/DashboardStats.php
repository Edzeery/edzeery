<?php

namespace App\Filament\Resources\Widgets;

use App\Enums\Store\StoreStatusEnum;
use App\Models\Products\Product;
use App\Models\Stores\Store;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Facades\Filament;

class DashboardStats extends StatsOverviewWidget
{
    protected static ?int $sort = -3;
    protected static bool $isLazy = true;
    protected function getStats(): array
    {
        return [
            Stat::make(
                'ACTIVED Stores',
                Store::where('status', StoreStatusEnum::ACTIVE)->count()
            )
                ->color("success")
                ->icon(StoreStatusEnum::ACTIVE->icon()),
            Stat::make(
                'PENDING Stores',
                Store::where('status', StoreStatusEnum::PENDING)->count()
            )
                ->color("worning"),
            Stat::make(
                'PENDING Stores',
                Store::where('status', StoreStatusEnum::CLOSED)->count()
            )
                ->color("success"),
            Stat::make(
                'All Stores',
                Store::all()->count()
            ),

            Stat::make(
                'Total Products',
                Product::all()->count()
            ),
        ];
    }
}
