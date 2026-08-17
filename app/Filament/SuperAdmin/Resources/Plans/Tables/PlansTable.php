<?php

namespace App\Filament\SuperAdmin\Resources\Plans\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PlansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('trial_days')
                    ->label('Trial'),

                TextColumn::make('max_stores'),

                TextColumn::make('prices.price')
                    ->label('Price')
                    ->formatStateUsing(
                        fn($state, $record) =>
                        optional($record->priceFor('monthly'))->price . ' ' . $record->currency
                    ),

                IconColumn::make('is_active')
                    ->boolean(),

                IconColumn::make('is_default')
                    ->boolean(),

                IconColumn::make('is_custom')
                    ->label('Custom')
                    ->boolean(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                TernaryFilter::make('is_custom')
                    ->label('Custom Plan'),
            ])
            ->recordActions([
                EditAction::make(), 
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
