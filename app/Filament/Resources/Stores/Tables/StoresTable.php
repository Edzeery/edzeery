<?php

namespace App\Filament\Resources\Stores\Tables;

use App\Enums\Store\StoreRoleEnum;
use App\Enums\Store\StoreStatusEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class StoresTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo')
                    ->label(__('Logo'))
                    ->disk('public')
                    ->visibility('public')
                    ->default('/img/icons/noimg.png')
                    ->circular()
                    ->size(50),

                ImageColumn::make('cover')
                    ->label(__('Cover'))
                    ->disk('public')
                    ->default('/img/icons/noimg.png')
                    ->circular()
                    ->size(50),
                TextColumn::make('user.name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('slug')
                    ->searchable(),
                TextColumn::make('subscription.plan.name')
                    ->searchable(),
                IconColumn::make('subscription.is_trail')
                    ->label('Is Trail'),

                SelectColumn::make('status')
                    ->options(
                        fn() => StoreStatusEnum::options()
                    ),

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
                ViewAction::make('view'),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
