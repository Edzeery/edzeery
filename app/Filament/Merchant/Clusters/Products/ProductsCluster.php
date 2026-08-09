<?php

namespace App\Filament\Merchant\Clusters\Products;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ProductsCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;
    protected static string|UnitEnum|null $navigationGroup = 'Store Management';
    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Start;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('titles.store_management');
    }

    public static function getNavigationLabel(): string
    {
        return __('titles.products_management');
    }

    public static function getModelLabel(): string
    {
        return __('titles.product_management');
    }
    public static function getPluralLabel(): ?string
    {
        return __('titles.products_management');
    }

}
