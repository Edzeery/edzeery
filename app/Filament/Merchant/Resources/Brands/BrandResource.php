<?php

namespace App\Filament\Merchant\Resources\Brands;

use App\Filament\Exports\BrandExporter;
use App\Filament\Imports\BrandImporter;
use App\Filament\Merchant\Clusters\Products\ProductsCluster;
use App\Filament\Merchant\Resources\Brands\Pages\ManageBrands;
use App\Models\Brand;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use UnitEnum;

class BrandResource extends Resource
{
    protected static ?string $cluster = ProductsCluster::class;

    protected static ?string $model = Brand::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;
    protected static string|UnitEnum|null $navigationGroup = 'Catalog';

    protected static ?int $navigationSort = 1; // غيّر الرقم حسب الترتيب

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $tenantOwnershipRelationshipName = 'store';


    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('titles.catalog');
    }

    public static function getModelLabel(): string
    {
        return __('titles.brand');
    }
    public static function getPluralLabel(): ?string
    {
        return __('titles.brands');
    }


    public static function getNavigationLabel(): string
    {
        return __('titles.brands');
    }

    public static function canCreate(): bool
    {
        return false;
    }


    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('store_id', currentStoreId());
    }

    public static function getNavigationBadge(): ?string
    {
        return optional(currentStore())->brands()?->count() ?? 0;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return Color::Blue;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Info')
                    ->schema([
                        Grid::make(2)->schema([
                            // Select::make('store_id')
                            //     ->relationship('store', 'name')
                            //     ->searchable()
                            //     ->preload()
                            //     ->required()
                            //     ->live()
                            //     ->hintIcon('heroicon-o-shield-check'),

                            TextInput::make('name')
                                ->label(__('titles.name'))
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(
                                    fn($state, callable $set) => $set('slug', Str::slug($state))
                                ),

                            TextInput::make('slug')->label(__('titles.slug'))->required()
                            // ->unique(ignoreRecord: true)
                            ,
                            Toggle::make('is_active')->default(true)
                                ->label('Status'),
                        ]),


                    ]),
                Section::make('Media & Status')
                    ->schema([


                        FileUpload::make('logo')
                            ->disk('public')
                            ->image()
                            ->reorderable()
                            ->directory('brands')
                            ->preserveFilenames(),
                    ]),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Info')
                    ->schema([
                        Grid::make(2)->schema([

                            TextEntry::make('name'),

                            TextEntry::make('slug'),



                            IconEntry::make('is_active')
                                ->boolean(),
                        ]),


                    ]),
                Section::make('Media & Date')
                    ->schema([

                        ImageColumn::make('logo')
                            ->disk('public')
                            ->circular(),
                        Grid::make(2)->schema(
                            [
                                TextEntry::make('created_at')
                                    ->dateTime()
                                    ->placeholder('-'),
                                TextEntry::make('updated_at')
                                    ->dateTime()
                                    ->placeholder('-'),
                            ]
                        )

                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                ImageColumn::make('logo')
                    ->label(__('titles.photo'))
                    ->disk('public') // أو disk التخزين لديك
                    ->default('/img/icons/noimg.png')
                    ->circular(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('slug')
                    ->searchable(),

                ToggleColumn::make('is_active'),
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
                Filter::make('is_active')
                    ->query(fn(Builder $query): Builder => $query->where('is_active', true))
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])->headerActions([
                ExportAction::make()
                    ->exporter(BrandExporter::class)
                    ->visible(canDo('store', 'export')),
                ImportAction::make('importBrands')
                    ->importer(BrandImporter::class)
                // ->visible(canDo('store', 'import'))

            ]);
    }


    public static function getPages(): array
    {
        return [
            'index' => ManageBrands::route('/'),
        ];
    }
}
