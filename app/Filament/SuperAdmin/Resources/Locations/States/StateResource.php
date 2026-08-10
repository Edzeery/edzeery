<?php

namespace App\Filament\SuperAdmin\Resources\Locations\States;

use App\Filament\Exports\StateExporter;
use App\Filament\Imports\StateImporter;
use App\Filament\SuperAdmin\Resources\Locations\States\Pages\ManageStates;
use App\Filament\SuperAdmin\Resources\Locations\States\RelationManagers\CitiesRelationManager;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;

use Kossa\AlgerianCities\Wilaya;
use Filament\Tables\Table;

use UnitEnum;

class StateResource extends Resource
{
    protected static ?string $model = State::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-map';
    protected static string|UnitEnum|null $navigationGroup = 'Locations';

    protected static ?int $navigationSort = 2;
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([

                Select::make('country_id')
                    ->relationship('country', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->reactive()
                    ->hintIcon('heroicon-o-shield-check'),

                Select::make('name')
                    ->label(__('titles.state_name'))
                    ->preload()
                    ->required()
                    ->reactive()
                    ->visible(fn($get) => $get('country_id') && optional(country($get('country_id')))->code === 'DZ')
                    ->options(fn() => states())
                    ->hintIcon('heroicon-o-shield-check'),

                Select::make('arabic_name')
                    ->label(__('titles.state_arabic_name'))
                    ->preload()
                    ->required()
                    ->reactive()
                    ->visible(fn($get) => $get('country_id') && optional(country($get('country_id')))->code === 'DZ')
                    ->options(fn() => states('arabic_name'))
                    ->default(fn($get) => $get('name') ? optional(state($get('name')))->arabic_name : null)
                    ->hintIcon('heroicon-o-shield-check'),


                TextInput::make('state_code')
                    ->required()
                    ->hintIcon('heroicon-o-shield-check')
                    ->maxLength(255)
                    ->default(fn($get) => $get('name'))
                    ->visible(fn($get) => $get('country_id') && optional(country($get('country_id')))->code === 'DZ'),


                TextInput::make('name')
                    ->label(__('titles.name'))
                    ->required()
                    ->hintIcon('heroicon-o-shield-check')
                    ->maxLength(255)
                    ->visible(fn($get) => $get('country_id') && optional(country($get('country_id')))->code  !== 'DZ'),

                TextInput::make('arabic_name')
                    ->label('Arabic Name')
                    ->required()
                    ->hintIcon('heroicon-o-shield-check')
                    ->maxLength(255)
                    ->visible(fn($get) => $get('country_id') && optional(country($get('country_id')))->code  !== 'DZ'),

                Toggle::make('is_cod_available')->default(true),
                Toggle::make('is_active')->default(true),

                TextInput::make('sort_order')->numeric()->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('id')->searchable(),
                TextColumn::make('country.name'),
                TextColumn::make('state_code')->searchable(),

                TextColumn::make('name')->searchable(),
                TextColumn::make('arabic_name')->searchable(),

                IconColumn::make('is_active')->boolean(),
                IconColumn::make('is_cod_available')->boolean(),
                IconColumn::make('sort_order'),
                IconColumn::make('longitude')->placeholder('-'),
                IconColumn::make('latitude')->boolean(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                ExportAction::make()
                    ->exporter(StateExporter::class)
                // ->visible(canDo('store', 'export'))
                ,
                ImportAction::make()
                    ->importer(StateImporter::class)
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

    public static function getRelations(): array
    {
        return [
            CitiesRelationManager::class,
        ];
    }


    public static function getPages(): array
    {
        return [
            'index' => ManageStates::route('/'),
        ];
    }
}
