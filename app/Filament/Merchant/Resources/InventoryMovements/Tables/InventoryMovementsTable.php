<?php

namespace App\Filament\Merchant\Resources\InventoryMovements\Tables;

use App\Enums\Store\InventoryMovementType;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class InventoryMovementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('variant.product.name')
                    ->label('Product')
                    ->searchable(),

                TextColumn::make('variant.sku')
                    ->label('SKU')
                    ->searchable(),

                TextColumn::make('type')
                    ->label(__('table.type'))
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
                    ->label(__('table.quantity'))
                    ->formatStateUsing(function ($state, $record) {
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
                    ->label(__('table.inventory_after'))
                    ->sortable()
                    ->color(fn($state) => $state <= 0 ? 'danger' : '')
                    ->weight('bold'),

                TextColumn::make('user.name')
                    ->label(__('titles.by'))
                    ->default('System')
                    ->tooltip(fn($record) => $record->user?->email),

            ])->searchable([
                'variant.sku',
                'variant.product.name',
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('type')
                    ->options(
                        collect(InventoryMovementType::cases())
                            ->mapWithKeys(fn($case) => [
                                $case->value => $case->label(),
                            ])
                    ),
            ])->actions([
                ViewAction::make(),
            ])
            ->recordAction(null)
            ->headerActions([]);
    }
}
