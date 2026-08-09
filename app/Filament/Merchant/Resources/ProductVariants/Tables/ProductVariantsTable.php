<?php

namespace App\Filament\Merchant\Resources\ProductVariants\Tables;

use App\Enums\Store\InventoryMovementType;
use App\Models\Products\ProductVariant;
use App\Services\InventoryService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductVariantsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label(__('table.product'))
                    ->searchable(),

                TextColumn::make('sku')
                    ->label(__('table.sku'))
                    ->searchable()
                    ->copyable(),

                TextColumn::make('price')->money('DZD'),
                TextColumn::make('compare_price')->money('DZD'),
                TextColumn::make('cost_price')->money('DZD'),

                TextColumn::make('stock')
                    ->label('Stock')
                    ->badge()
                    ->state(
                        fn(ProductVariant $record) =>
                        match ($record->stockStatus()) {
                            'out' => 'OUT',
                            'low' => 'LOW',
                            default => 'IN',
                        }
                    )
                    ->color(
                        fn(ProductVariant $record) =>
                        match ($record->stockStatus()) {
                            'out' => 'danger',
                            'low' => 'warning',
                            default => 'success',
                        }
                    )
                    ->tooltip(
                        fn(ProductVariant $record) =>
                        "Stock: {$record->stock} | Threshold: {$record->low_stock_threshold}"
                    ),



                TextColumn::make('created_at')->since(),
            ])
            ->filters([
                //
            ])
            ->recordActions([

                Action::make('applyStock')
                    ->label('Apply Stock Movement')
                    ->icon('heroicon-o-arrows-up-down')
                    ->form([
                        TextInput::make('quantity')
                            ->numeric()
                            ->required(),

                        Select::make('type')
                            ->options(InventoryMovementType::manualOptions())
                            ->required(),

                    ])
                    ->action(function (array $data, ProductVariant $record) {
                        $type = InventoryMovementType::from($data['type']);

                        InventoryService::apply(
                            $record,
                            (int) $data['quantity'],
                            $type,
                            auth()->user()
                        );
                    })->successNotificationTitle('Stock adjusted successfully'),

                EditAction::make(),
                ViewAction::make()

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
