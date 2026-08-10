<?php

namespace App\Filament\SuperAdmin\Resources\Locations\States\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;

class CitiesRelationManager extends RelationManager
{
    protected static string $relationship = 'cities';

    public function form(Schema $form): Schema
    {
        return $form->schema([
            Select::make('store_id')
                ->relationship('store', 'name')
                ->searchable()
                ->default(currentStore()->id)
                ->preload()
                ->required()
                ->live()
                ->hintIcon('heroicon-o-shield-check'),

            TextInput::make('name')->required(),

            Toggle::make('is_cod_available')->default(true),
            Toggle::make('is_active')->default(true),

            TextInput::make('sort_order')->numeric()->default(0),
        ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
                IconColumn::make('is_cod_available')->boolean(),
                IconColumn::make('is_active')->boolean(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
