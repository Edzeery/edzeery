<?php

namespace App\Filament\SuperAdmin\Resources\Plans\RelationManagers;

use App\Models\Plans\PlanFeature;
use Filament\Actions\AttachAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DetachAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PlanFeaturesRelationManager extends RelationManager
{

    protected static string $relationship = 'features';

    protected static ?string $title = 'Features';

    /* =========================
     | FORM (v4)
     ========================= */

    public function form(Schema $form): schema
    {
        return $form->schema([
            Select::make('recordId')
                ->label('Feature')
                ->options(PlanFeature::pluck('name', 'id'))
                ->searchable()
                ->required(),

            TextInput::make('value')
                ->placeholder('100 / unlimited / true'),

            TextInput::make('charges')
                ->numeric()
                ->label('Extra Charges'),
        ]);
    }

    /* =========================
     | TABLE (v4)
     ========================= */
    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Feature'),
                TextColumn::make('pivot.value')->label('Value'),
                TextColumn::make('pivot.charges')->label('Charges'),

            ])
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->form(fn($action) => [
                        TextInput::make('value'),
                        TextInput::make('charges')->numeric(),
                    ]),

            ])
            ->recordActions([
                EditAction::make()
                    ->form([
                        TextInput::make('value'),
                        TextInput::make('charges')->numeric(),
                    ]),
                DetachAction::make(),

            ]);
    }
}
