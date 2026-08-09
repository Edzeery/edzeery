<?php

namespace App\Filament\Merchant\Resources\Inventories;

use App\Filament\Merchant\Clusters\Products\ProductsCluster;
use App\Filament\Merchant\Resources\Inventories\Pages\ListInventory;
use App\Filament\Merchant\Resources\Inventories\Widgets\InventoryOverview;
use App\Filament\Merchant\Resources\InventoryMovements\InventoryMovementResource;

use App\Models\Products\ProductVariant;
use App\Services\InventoryService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class InventoryResource extends Resource
{
    protected static ?string $cluster = ProductsCluster::class;
    protected static ?string $model = ProductVariant::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationLabel = 'Inventory management';


    protected static ?string $recordTitleAttribute = 'name';
    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('titles.products_management');
    }

    public static function getModelLabel(): string
    {
        return __('titles.inventory');
    }
    public static function getPluralLabel(): ?string
    {
        return __('titles.inventories');
    }

    public static function getNavigationLabel(): string
    {
        return __('titles.inventory_management');
    }

    public static function getTitleCaseModelLabel(): string
    {
        return  __('titles.inventory_management');
    }

    public static function getTitleCasePluralModelLabel(): string
    {
        return  __('titles.inventory_management');
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('stock')
                    ->label('Stock')
                    ->sortable()
                    ->color(fn($state) => $state <= 0 ? 'danger' : 'success')
                    ->weight('bold'),

                TextColumn::make('stock_status')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(fn($record) => match ($record->stockStatus()) {
                        'out' => 'Out of stock',
                        'low' => 'Low stock',
                        default => 'In stock',
                    })
                    ->color(fn($record) => match ($record->stockStatus()) {
                        'out' => 'danger',
                        'low' => 'warning',
                        default => 'success',
                    }),

            ])
            ->actions([
                Action::make('adjust')
                    ->label('Adjust Stock')
                    ->icon('heroicon-o-pencil-square')
                    ->modalHeading('Adjust Inventory')
                    ->form([
                        TextInput::make('quantity')
                            ->numeric()
                            ->required()
                            ->label('New Quantity'),

                        Textarea::make('reason')
                            ->label('Reason')
                            ->maxLength(255),
                    ])
                    ->action(function ($record, array $data) {
                        InventoryService::adjust(
                            $record,
                            (int) $data['quantity'],
                            $data['reason'] ?? null,
                        );
                    }),

                Action::make('history')
                    ->label('Movements')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->url(
                        fn($record) =>
                        InventoryMovementResource::getUrl('index', [
                            'tableFilters[variant_id][value]' => $record->id,
                        ])
                    ),
            ])
            ->defaultSort('stock', 'asc')
            ->recordAction(null);
    }
    public static function getWidgets(): array
    {
        return [
            InventoryOverview::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInventory::route('/'),
        ];
    }
    public static function canCreate(): bool
    {
        return false;
    }
}
