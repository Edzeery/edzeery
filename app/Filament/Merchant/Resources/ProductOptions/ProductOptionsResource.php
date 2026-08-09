<?php

namespace App\Filament\Merchant\Resources\ProductOptions;

use App\Filament\Merchant\Clusters\Products\ProductsCluster;
use App\Filament\Merchant\Resources\ProductOptions\Pages\CreateProductOptions;
use App\Filament\Merchant\Resources\ProductOptions\Pages\EditProductOptions;
use App\Filament\Merchant\Resources\ProductOptions\Pages\ListProductOptions;
use App\Filament\Merchant\Resources\ProductOptions\RelationManagers\ValuesRelationManager;
use App\Filament\Merchant\Resources\ProductOptions\Schemas\ProductOptionsForm;
use App\Filament\Merchant\Resources\ProductOptions\Tables\ProductOptionsTable;
use App\Models\Products\ProductOption;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class ProductOptionsResource extends Resource
{
    protected static ?string $model = ProductOption::class;
    protected static ?string $cluster = ProductsCluster::class;
    protected static string|UnitEnum|null $navigationGroup = 'Catalog';
    protected static ?int $navigationSort = 3;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('titles.catalog');
    }
    public static function getModelLabel(): string
    {
        return __('titles.product_option');
    }
    public static function getPluralLabel(): ?string
    {
        return __('titles.product_options');
    }

    public static function form(Schema $schema): Schema
    {
        return ProductOptionsForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductOptionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ValuesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProductOptions::route('/'),
            'edit' => EditProductOptions::route('/{record}/edit'),
        ];
    }
}
