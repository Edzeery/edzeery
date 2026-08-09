<?php

namespace App\Filament\Merchant\Resources\Products;

use App\Filament\Merchant\Clusters\Products\ProductsCluster;
use App\Filament\Merchant\Resources\Products\Pages\CreateProduct;
use App\Filament\Merchant\Resources\Products\Pages\EditProduct;
use App\Filament\Merchant\Resources\Products\Pages\ListProducts;
use App\Filament\Merchant\Resources\Products\RelationManagers\VariantsRelationManager;
use App\Filament\Merchant\Resources\Products\Schemas\ProductForm;
use App\Filament\Merchant\Resources\Products\Tables\ProductsTable;
use App\Models\Products\Product;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $cluster = ProductsCluster::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cube';
    protected static string|UnitEnum|null $navigationGroup = 'Products Management';

    protected static ?int $navigationSort = 1;
    
    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('titles.products_management');
    }


    public static function getModelLabel(): string
    {
        return __('titles.product');
    }

    public static function getPluralLabel(): ?string
    {
        return __('titles.products');
    }


    public static function getNavigationLabel(): string
    {
        return __('titles.products');
    }



    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('store_id', currentStoreId());
    }

    public static function getNavigationBadge(): ?string
    {
        return optional(currentStore())->products()?->count() ?? 0;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return Color::Blue;
    }

    protected static ?string $recordTitleAttribute = 'name';


    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'The number of Merchants';
    }

    protected static function booted()
    {
        static::addGlobalScope('store', function ($query) {
            if (currentStoreId()) {
                $query->where('user_id', user()->id);
            }
        });
    }

    public static function form(Schema $schema): Schema
    {
        return ProductForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            VariantsRelationManager::class,
        ];
    }


    public static function getPages(): array
    {
        return [
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }
}
