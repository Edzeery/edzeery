<?php

namespace App\Filament\Merchant\Resources\StockAlerts;

use App\Filament\Merchant\Clusters\Products\ProductsCluster;
use App\Filament\Merchant\Resources\StockAlerts\Pages;
use App\Models\Products\ProductVariant;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class StockAlertResource extends Resource
{
    protected static ?string $cluster = ProductsCluster::class;
    protected static ?string $model = ProductVariant::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static ?string $navigationLabel = 'Stock Alerts';

    protected static string|UnitEnum|null $navigationGroup = 'Products Management';

    protected static ?int $navigationSort = 5;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('titles.products_management');
    }

    public static function getTitleCaseModelLabel(): string
    {
        return __('titles.stock_alerts');
    }

    public static function getTitleCasePluralModelLabel(): string
    {
        return __('titles.stock_alerts');
    }


    public static function getNavigationLabel(): string
    {
        return __('titles.stock_alerts');
    }

    public static function canCreate(): bool
    {
        return false;
    }



    public static function table(Table $table): Table
    {
        return $table
            ->query(
                ProductVariant::query()
                    ->where(function ($q) {
                        $q->where('stock', '<=', 0)
                            ->orWhereColumn('stock', '<=', 'low_stock_threshold');
                    })
            )
            ->columns([
                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),

                Tables\Columns\TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable(),

                Tables\Columns\TextColumn::make('stock')
                    ->badge()
                    ->color(fn($record) => match ($record->stockStatus()) {
                        'out' => 'danger',
                        'low' => 'warning',
                        default => 'success',
                    }),

                Tables\Columns\TextColumn::make('low_stock_threshold')
                    ->label('Threshold'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->state(fn($record) => strtoupper($record->stockStatus()))
                    ->badge()
                    ->color(fn($record) => match ($record->stockStatus()) {
                        'out' => 'danger',
                        'low' => 'warning',
                        default => 'success',
                    }),
            ])
            ->actions([
                Action::make('adjust')
                    ->label('Adjust Stock')
                    ->icon('heroicon-o-arrows-up-down')
                    ->url(
                        fn($record) =>
                        route(
                            'filament.merchant.products.resources.product-variants.index',
                            [currentStore(),  $record]
                        )
                    ),
            ])
            ->defaultSort('stock', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockAlerts::route('/'),
        ];
    }
}
