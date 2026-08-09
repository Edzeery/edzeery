<?php

namespace App\Filament\Resources\Locations\Countries;

use App\Filament\Resources\Locations\Countries\Pages\ManageCountries;
use App\Filament\Resources\Locations\Countries\RelationManagers\StatesRelationManager;

use App\Filament\Exports\CountryExporter;
use App\Filament\Imports\CountryImporter;
use App\Models\Locations\Country;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

class CountryResource extends Resource
{
    protected static ?string $model = Country::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-globe-alt';

    protected static string|UnitEnum|null  $navigationGroup = 'Locations';
    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([

                TextInput::make('name')
                    ->label(__('titles.name'))
                    ->required()
                    ->hintIcon('heroicon-o-shield-check')
                    ->maxLength(255),

                TextInput::make('arabic_name')
                    ->required()
                    ->hintIcon('heroicon-o-shield-check')
                    ->maxLength(255),

                TextInput::make('code')
                    ->maxLength(5),

                Toggle::make('is_active')
                    ->default(true),

                Toggle::make('is_cod_available')
                    ->label('Cash on Delivery')
                    ->default(true),

                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('arabic_name')->searchable(),
                TextColumn::make('code'),
                ToggleColumn::make('is_active'),
                ToggleColumn::make('is_cod_available'),
                TextColumn::make('sort_order')->sortable(),
            ])
            ->defaultSort('sort_order')

            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exporter(CountryExporter::class)
                // ->visible(canDo('store', 'export'))
                ,
                ImportAction::make()
                    ->importer(CountryImporter::class)
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
            StatesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCountries::route('/'),
        ];
    }
}
