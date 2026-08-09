<?php

namespace App\Filament\Merchant\Resources\ProductVariants\RelationManagers;

use App\Enums\Store\InventoryMovementType;
use App\Models\Products\ProductVariant;
use App\Services\InventoryService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class InventoryMovementsRelationManager extends RelationManager
{
    protected static string $relationship = 'inventoryMovements';

    protected static ?string $title = 'Stock History';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('type')
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

                TextColumn::make('quantity')
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


                TextColumn::make('balance_after')
                    ->label('Stock After'),

                TextColumn::make('Merchant.name')
                    ->label('By')
                    ->default('System')
                    ->tooltip(fn($record) => $record->Merchant?->email),

            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50])

            ->recordAction(
                null
            )      // ❌ no view/edit
            ->headerActions([])       // ❌ no create
            ->actions([])             // ❌ no edit/delete
            ->bulkActions([]);        // ❌ no bulk
    }
}
