<?php

namespace App\Filament\Merchant\Resources\Products\RelationManagers;

use App\Enums\Store\InventoryMovementType;
use App\Filament\Merchant\Resources\Products\ProductResource;
use App\Models\Products\ProductVariant;
use App\Services\InventoryService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VariantsRelationManager extends RelationManager
{

    protected static string $relationship = 'variants';

    protected static ?string $relatedResource = ProductResource::class;

    protected static ?string $recordTitleAttribute = 'slug';

    public function table(Table $table): Table
    {

        return $table
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('sku'),
                TextColumn::make('barcode'),
                TextColumn::make('price'),
                TextColumn::make('cost_price'),
                TextColumn::make('stock')
                    ->label('Stock')
                    ->sortable()
                    ->badge()
                    ->state(
                        fn(ProductVariant $record) =>
                        match ($record->stockStatus()) {
                            'out' => 'OUT (0)',
                            'low' => "LOW ({$record->stock})",
                            default => "IN ({$record->stock})",
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
                    ->weight('bold'),

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
                            ->options(
                                collect(InventoryMovementType::cases())
                                    ->filter(fn(InventoryMovementType $case) => $case->options())
                                    ->mapWithKeys(fn(InventoryMovementType $case) => [
                                        $case->value => $case->label(),
                                    ])
                                    ->toArray()
                            )
                            ->required(),

                    ])
                    ->action(function (array $data, ProductVariant $record) {
                        $type = InventoryMovementType::from($data['type']);

                        InventoryService::apply(
                            $record,
                            (int) $data['quantity'],
                            $type,
                            user()
                        );
                    })->successNotificationTitle('Stock adjusted successfully'),
            ])
            ->recordAction(null)      // ❌ no view/edit
            ->headerActions([])       // ❌ no create

            ->bulkActions([]);        // ❌ no bulk
    }
}
