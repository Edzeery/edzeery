<?php

namespace App\Filament\Merchant\Resources\InventoryMovements;

use App\Enums\Store\InventoryMovementType;
use App\Filament\Merchant\Clusters\Products\ProductsCluster;
use App\Filament\Merchant\Resources\InventoryMovements\Pages\CreateInventoryMovement;
use App\Filament\Merchant\Resources\InventoryMovements\Pages\EditInventoryMovement;
use App\Filament\Merchant\Resources\InventoryMovements\Pages\ListInventoryMovements;
use App\Filament\Merchant\Resources\InventoryMovements\Pages\ViewInventoryMovement;
use App\Filament\Merchant\Resources\InventoryMovements\Schemas\InventoryMovementForm;
use App\Filament\Merchant\Resources\InventoryMovements\Schemas\InventoryMovementInfolist;
use App\Filament\Merchant\Resources\InventoryMovements\Tables\InventoryMovementsTable;
use App\Models\InventoryMovement;
use BackedEnum;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class InventoryMovementResource extends Resource
{
    protected static ?string $cluster = ProductsCluster::class;
    protected static ?string $model = InventoryMovement::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationLabel = 'Inventory Movements';
    protected static string|UnitEnum|null $navigationGroup = 'Products Management';

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('titles.products_management');
    }

    public static function getModelLabel(): string
    {
        return __('titles.inventory_movement');
    }
    public static function getPluralLabel(): ?string
    {
        return __('titles.inventory_movements');
    }

    public static function getTitleCaseModelLabel(): string
    {
        return __('titles.inventory_movement');
    }

    public static function getTitleCasePluralModelLabel(): string
    {
        return __('titles.inventory_movements');
    }


    public static function getNavigationLabel(): string
    {
        return __('titles.inventory_movements');
    }


    protected static ?int $navigationSort = 4;
    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function infolist(Schema $infolist): Schema
    {
        return $infolist
            ->schema([
                Section::make('Movement Details')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('type')
                            ->badge()
                            ->formatStateUsing(
                                fn($state) =>
                                InventoryMovementType::from(is_string($state) ? $state : $state->value)->label()
                            )
                            ->color(
                                fn($state) =>
                                InventoryMovementType::from(is_string($state) ? $state : $state->value)->color()
                            )
                            ->icon(
                                fn($state) =>
                                InventoryMovementType::from(is_string($state) ? $state : $state->value)->icon()
                            ),


                        TextEntry::make('created_at')
                            ->label('Date')
                            ->dateTime(),

                        TextEntry::make('quantity')
                            ->label('Quantity')
                            ->formatStateUsing(function ($state, $record) {
                                // إذا كان Enum بالفعل، استخدمه مباشرة، وإلا حوله من string
                                $type = $record->type instanceof InventoryMovementType
                                    ? $record->type
                                    : InventoryMovementType::from($record->type);

                                $sign = $type->isDecrease() ? '-' : '+';

                                return $sign . $state;
                            })
                            ->color(function ($state, $record) {
                                $type = $record->type instanceof InventoryMovementType
                                    ? $record->type
                                    : InventoryMovementType::from($record->type);

                                return $type->isDecrease() ? 'danger' : 'success';
                            }),


                        TextEntry::make('balance_after')
                            ->label('Stock After'),

                        TextEntry::make('Merchant.name')
                            ->label('Performed By')
                            ->placeholder('System')
                            ->tooltip(fn($record) => $record->Merchant?->email),

                    ]),

                Section::make('Product Information')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('variant.product.name')
                            ->label('Product'),

                        TextEntry::make('variant.sku')
                            ->label('SKU'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return InventoryMovementsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    public static function getPages(): array
    {
        return [
            'index' => ListInventoryMovements::route('/'),
        ];
    }
}
