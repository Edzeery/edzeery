<?php

namespace App\Filament\Merchant\Resources\Categories;

use App\Filament\Exports\CategoryExporter;
use App\Filament\Imports\CategoryImporter;
use App\Filament\Merchant\Clusters\Products\ProductsCluster;
use App\Filament\Merchant\Resources\Categories\Pages\ManageCategories;
use App\Models\Category;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\TextListEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class CategoryResource extends Resource
{
    protected static ?string $cluster = ProductsCluster::class;
    protected static ?string $model = Category::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ?string $tenantOwnershipRelationshipName = 'store';

    protected static string|UnitEnum|null $navigationGroup = 'Catalog';
    protected static ?int $navigationSort = 2;
    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('titles.catalog');
    }
    public static function getModelLabel(): string
    {
        return __('titles.category');
    }
    public static function getPluralLabel(): ?string
    {
        return __('titles.categories');
    }
    public static function getNavigationLabel(): string
    {
        return __('titles.categories');
    }
    public static function getTitleCaseModelLabel(): string
    {
        return __('titles.category');
    }
    public static function getTitleCasePluralModelLabel(): string
    {
        return __('titles.categories');
    }
    public static function getNavigationBadge(): ?string
    {
        return optional(currentStore())->categories()?->count() ?? 0;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return Color::Blue;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('parent_id')
                    ->label('Parent Category')
                    ->relationship('parent', 'name')
                    ->options(function () {
                        return currentStore()?->categories()?->pluck('name', 'id') ?? [];
                    })
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->hint('اختر الفئة الأب إذا كانت هذه فئة فرعية'),

                TextInput::make('name')
                    ->required(),

                TextInput::make('slug')
                    ->required(),

                Toggle::make('is_active')->default(true),

                FileUpload::make('logo')
                    ->directory('categories')
                    ->disk('public')
                    ->image()
                    ->preserveFilenames(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('store.name')
                    ->label('Store')
                    ->placeholder('-'),

                TextEntry::make('full_name')
                    ->label('Category Hierarchy'),

                TextEntry::make('slug'),
                TextEntry::make('logo')
                    ->placeholder('-'),

                IconEntry::make('is_active')
                    ->boolean(),

                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),

                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                ImageColumn::make('logo')
                    ->label(__('titles.photo'))
                    ->disk('public')
                    ->default('/img/icons/noimg.png')
                    ->circular()
                    ->size(50),

                TextColumn::make('store.name')
                    ->searchable(),

                TextColumn::make('full_name')
                    ->label('Category Hierarchy')
                    ->searchable()
                    ->sortable(),

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
            ->headerActions([
                ExportAction::make()
                    ->exporter(CategoryExporter::class)
                    ->visible(canDo('store', 'export')),

                ImportAction::make()
                    ->importer(CategoryImporter::class)
                    ->visible(canDo('store', 'import'))
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
            'index' => ManageCategories::route('/'),
        ];
    }
}
