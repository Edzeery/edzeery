<?php

namespace App\Filament\Resources\Plans\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PlanPricesRelationManager extends RelationManager
{

    protected static string $relationship = 'prices';

    protected static ?string $title = 'Prices';

    /* =========================
     | FORM (v4)
     ========================= */

    public function form(Schema $form): schema
    {
        return $form->schema([
            Select::make('billing_period')
                ->options([
                    'monthly' => 'Monthly',
                    'yearly' => 'Yearly',
                ])
                ->required(),

            TextInput::make('price')
                ->numeric()
                ->required(),

            TextInput::make('duration')
                ->numeric()
                ->required()
                ->helperText('Days (30 / 365)'),

            Toggle::make('is_active')
                ->default(true),
        ]);
    }

    /* =========================
     | TABLE (v4)
     ========================= */
    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('billing_period'),
                TextColumn::make('price'),
                TextColumn::make('duration'),
                IconColumn::make('is_active')->boolean(),

            ])
            ->headerActions([
                CreateAction::make(),

            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),

            ]);
    }
}
