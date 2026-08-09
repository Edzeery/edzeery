<?php

namespace App\Filament\Resources\Locations\Cities;

use App\Filament\Exports\CityExporter;
use App\Filament\Imports\CityImporter;
use App\Filament\Resources\Locations\Cities\Pages\ManageCities;
use App\Models\Locations\City;
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
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use UnitEnum;

class CityResource extends Resource
{
    protected static ?string $model = City::class;

    protected static string|BackedEnum|null  $navigationIcon = 'heroicon-o-building-office';
    protected static string|UnitEnum|null $navigationGroup = 'Locations';
    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([


                Select::make('state_id')
                    ->relationship('state', 'name')
                    ->searchable()
                    ->preload()
                    ->live()
                    ->required()
                    ->hintIcon('heroicon-o-shield-check'),

                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                Toggle::make('is_cod_available')
                    ->label('Cash on Delivery')
                    ->default(true),

                Toggle::make('is_active')
                    ->default(true),

                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('arabic_name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('post_code')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('city_code')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('state.name')
                    ->label('State')
                    ->sortable(),

                TextColumn::make('state.country.name')
                    ->label('Country')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),

                IconColumn::make('is_cod_available')
                    ->boolean()
                    ->label('COD'),


                TextColumn::make('sort_order')
                    ->sortable(),

            ])
            ->defaultSort('sort_order')
            ->filters([
                TernaryFilter::make('is_cod_available')
                    ->label('COD'),

                TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exporter(CityExporter::class)
                // ->visible(canDo('store', 'export'))
                ,
                ImportAction::make()
                    ->importer(CityImporter::class)
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCities::route('/'),
        ];
    }
}
