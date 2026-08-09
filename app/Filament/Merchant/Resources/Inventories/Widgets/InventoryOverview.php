<?php

namespace App\Filament\Merchant\Resources\Inventories\Widgets;

use App\Models\InventoryMovement;
use App\Models\Products\ProductVariant;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class InventoryOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make(
                'Total Stock',
                ProductVariant::sum('stock')
            )
                ->icon('heroicon-o-archive-box')
                ->color('success'),

            Stat::make(
                'Low Stock',
                ProductVariant::query()
                    ->where('stock', '>', 0)
                    ->whereColumn('stock', '<=', 'low_stock_threshold')
                    ->count()
            )
                ->icon('heroicon-o-exclamation-triangle')
                ->color('warning'),

            Stat::make(
                'Out of Stock',
                ProductVariant::where('stock', '<=', 0)->count()
            )
                ->icon('heroicon-o-x-circle')
                ->color('danger'),

            Stat::make(
                'Movements (7 days)',
                InventoryMovement::where(
                    'created_at',
                    '>=',
                    now()->subDays(7)
                )->count()
            )
                ->icon('heroicon-o-arrows-right-left')
                ->color('info'),
        ];
    }
}
