<?php

namespace App\Filament\Merchant\Resources\Products\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProductOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {

        // إجمالي المنتجات
        $totalProducts = currentStore()->products()->count();

        // المنتجات النشطة
        $activeProducts = currentStore()
            ->products()
            ->where('is_active', true)
            ->count();

        // المنتجات المتغيرة (variants)
        $variableProducts = currentStore()
            ->products()
            ->where('type', 'variable')
            ->count();

        return [
            Stat::make('Total Products', $totalProducts)
                ->icon('heroicon-o-archive-box')
                ->color('primary'),

            Stat::make('Active Products', $activeProducts)
                ->icon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('Variable Products', $variableProducts)
                ->icon('heroicon-o-cube')
                ->color('warning'),
        ];
    }
}
