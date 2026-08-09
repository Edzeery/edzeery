<?php

namespace App\Filament\Merchant\Resources\ProductVariants;

use App\Filament\Merchant\Clusters\Products\ProductsCluster;
use App\Filament\Merchant\Resources\ProductVariants\Pages\CreateProductVariant;
use App\Filament\Merchant\Resources\ProductVariants\Pages\EditProductVariant;
use App\Filament\Merchant\Resources\ProductVariants\Pages\ListProductVariants;
use App\Filament\Merchant\Resources\ProductVariants\Pages\ViewProductVariant;
use App\Filament\Merchant\Resources\ProductVariants\RelationManagers\InventoryMovementsRelationManager;
use App\Filament\Merchant\Resources\ProductVariants\Schemas\ProductVariantForm;
use App\Filament\Merchant\Resources\ProductVariants\Schemas\ProductVariantInfolist;
use App\Filament\Merchant\Resources\ProductVariants\Tables\ProductVariantsTable;
use App\Models\Products\ProductVariant;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ProductVariantResource extends Resource
{
    protected static ?string $model = ProductVariant::class;
    protected static ?string $cluster = ProductsCluster::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?string $navigationLabel = 'Variants';

    protected static string|UnitEnum|null $navigationGroup = 'Products Management';

    // protected static ?string $navigationParentItem = "Products";

    // protected static ?string $recordTitleAttribute = 'sku';

    protected static ?int $navigationSort = 2; // غيّر الرقم حسب الترتيب

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('titles.products_management');
    }

     public static function getNavigationLabel(): string
    {
        return __('titles.variants');
    }

    public static function getModelLabel(): string
    {
        return __('titles.variant');
    }

    public static function getPluralLabel(): ?string
    {
        return __('titles.variants');
    }


    public static function form(Schema $schema): Schema
    {
        return ProductVariantForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ProductVariantInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductVariantsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            InventoryMovementsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProductVariants::route('/'),
            'create' => CreateProductVariant::route('/create'),
            'edit' => EditProductVariant::route('/{record}/edit'),
        ];
    }
}
