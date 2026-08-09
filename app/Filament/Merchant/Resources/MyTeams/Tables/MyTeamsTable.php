<?php

namespace App\Filament\Merchant\Resources\MyTeams\Tables;

use App\Enums\Platform\UserRoleEnum;
use App\Enums\Store\StoreRoleEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class MyTeamsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Name')
                    ->searchable(),

                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable(),

               TextColumn::make('user.merchantRole.name')
                    ->label('Role in this stor')
                    ->badge()
                    ->formatStateUsing(

                        fn($state) =>   in_array($state, UserRoleEnum::values()) ? UserRoleEnum::from($state)?->label() :  StoreRoleEnum::from($state)?->label()
                    )
                    ->color(
                        fn($state) => in_array($state, UserRoleEnum::values()) ?  UserRoleEnum::from($state)?->filamentColor() :  StoreRoleEnum::from($state)?->filamentColor()
                    )
                    ->icon(
                        fn($state) =>
                        in_array($state, UserRoleEnum::values()) ?  UserRoleEnum::from($state)?->icon() :  StoreRoleEnum::from($state)?->icon()

                    ),

                TextColumn::make('user.country.name')->sortable()->searchable(),
                TextColumn::make('user.state.name')->sortable()->searchable(),
                TextColumn::make('user.city.name')->sortable()->searchable(),

                IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn($record) => canModifyMember($record)),

                DeleteAction::make()
                    ->visible(fn($record) => canModifyMember($record)),

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
