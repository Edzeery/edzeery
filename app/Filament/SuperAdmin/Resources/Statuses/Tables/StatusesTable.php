<?php

namespace App\Filament\SuperAdmin\Resources\Statuses\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StatusesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable(),
                TextColumn::make('store.name')
                    ->searchable(),
                TextColumn::make('store_scope_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('type')
                    ->searchable(),
                TextColumn::make('key')
                    ->searchable(),
                TextColumn::make('label')
                    ->searchable(),
                TextColumn::make('color')
                    ->searchable(),
                IconColumn::make('is_system')
                    ->boolean(),
                IconColumn::make('affects_inventory')
                    ->boolean(),
                TextColumn::make('movement_type')
                    ->searchable(),
                TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
