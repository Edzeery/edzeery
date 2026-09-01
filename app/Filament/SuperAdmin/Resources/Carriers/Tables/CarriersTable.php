<?php

namespace App\Filament\SuperAdmin\Resources\Carriers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CarriersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('code')
                    ->searchable(),
                TextColumn::make('platform.name')
                    ->label('Platform')
                    ->searchable()
                    ->placeholder('—'),
                TextColumn::make('credential_fields')
                    ->formatStateUsing(fn ($state) => is_array($state) ? implode(', ', array_column($state, 'key')) : '—'),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([

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