<?php

namespace App\Filament\SuperAdmin\Resources\Users\Tables;

use App\Enums\Platform\UserRoleEnum;
use App\Enums\Store\StoreRoleEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('name')
                    ->searchable()
                    ->color('primary'),

                TextColumn::make('email')
                    ->label('Email address')
                    ->color(Color::Gray)
                    ->icon(Heroicon::Envelope)
                    ->searchable(),

                TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable(),

                ViewColumn::make('stores')
                    ->label(__('titles.store'))
                    ->view('filament.tables.columns.user-stores'),

                TextColumn::make('merchantRole.name')
                    ->label('Role in stor')
                    ->badge()
                    ->formatStateUsing(

                        fn ($state) => in_array($state, UserRoleEnum::values()) ? UserRoleEnum::from($state)?->label() : StoreRoleEnum::from($state)?->label()
                    )
                    ->color(
                        fn ($state) => in_array($state, UserRoleEnum::values()) ? UserRoleEnum::from($state)?->filamentColor() : StoreRoleEnum::from($state)?->filamentColor()
                    )
                    ->icon(
                        fn ($state) => in_array($state, UserRoleEnum::values()) ? UserRoleEnum::from($state)?->filamentIcon() : StoreRoleEnum::from($state)?->filamentIcon()

                    ),

                TextColumn::make('roles.name')
                    ->badge()
                    ->listWithLineBreaks()
                    ->formatStateUsing(

                        fn ($state) => in_array($state, UserRoleEnum::values()) ? UserRoleEnum::from($state)?->label() : StoreRoleEnum::from($state)?->label()
                    )
                    ->color(
                        fn ($state) => in_array($state, UserRoleEnum::values()) ? UserRoleEnum::from($state)?->filamentColor() : StoreRoleEnum::from($state)?->filamentColor()
                    )
                    ->icon(
                        fn ($state) => in_array($state, UserRoleEnum::values()) ? UserRoleEnum::from($state)?->filamentIcon() : StoreRoleEnum::from($state)?->filamentIcon()

                    ),

                TextColumn::make('country.name')

                    ->searchable(),
                TextColumn::make('state.name')
                    ->searchable(),
                TextColumn::make('city.name')
                    ->searchable(),
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
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
