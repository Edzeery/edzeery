<?php

namespace App\Filament\Merchant\Resources\ProductOptionValues;

use App\Filament\Exports\ProductOptionValueExporter;
use App\Filament\Imports\ProductOptionValueImporter;
use App\Filament\Merchant\Clusters\Products\ProductsCluster;
use App\Filament\Merchant\Resources\ProductOptionValues\Pages\ManageProductOptionValues;
use App\Models\Products\ProductOption;
use App\Models\Products\ProductOptionValue;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class ProductOptionValueResource extends Resource
{
    protected static ?string $cluster = ProductsCluster::class;
    protected static ?string $model = ProductOptionValue::class;
    protected static string|UnitEnum|null $navigationGroup = 'Catalog';

    protected static string $relationship = 'values';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationParentItem = "Product Options";
    protected static ?int $navigationSort = 4;
    public static function getNavigationLabel(): string
    {
        return __('titles.option_values');
    }

    public static function getModelLabel(): string
    {
        return __('titles.option_value');
    }


    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Select::make('product_option_id')->relationship('option', 'name')->required(),

            TextInput::make('value')
                ->required()
                ->maxLength(100)
                ->label(
                    fn($record) =>
                    match ($record?->name) {
                        'color' => 'Color name (Red, Black)',
                        'size'  => 'Size (40, 42, S, L)',
                        default => 'Value',
                    }
                )->unique(
                    table: 'product_option_values',
                    column: 'value',
                    ignoreRecord: true,
                    modifyRuleUsing: fn($rule, callable $get) =>
                    $rule->where(
                        'product_option_id',
                        $get('product_option_id')
                    )
                )


        ]);
    }

    public static function table(Table $table): Table
    {
        return $table

            ->recordTitleAttribute('value')
            ->columns([
                TextColumn::make('option.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('value')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('variants_count')
                    ->counts('variants')
                    ->label('Used in Variants'),
                TextColumn::make('stock')
                    ->label('Stock')
                    ->badge()
                    ->state(function (ProductOptionValue $record) {
                        $stock = $record->variants()->sum('stock');
                        return match (true) {
                            $stock <= 0  => 'OUT (0)',
                            $stock <= 5  => "LOW ({$stock})",
                            default      => "IN ({$stock})",
                        };
                    })
                    ->color(function (ProductOptionValue $record) {
                        $stock = $record->variants()->sum('stock');

                        return match (true) {
                            $stock <= 0 => 'danger',
                            $stock <= 5 => 'warning',
                            default     => 'success',
                        };
                    })
                    ->weight('bold'),
            ])
            ->filters([
                SelectFilter::make('product_option_id')
                    ->relationship('option', 'name')
                    ->label(__('titles.option'))
                    ->searchable()
                    ->preload(),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exporter(ProductOptionValueExporter::class)
                // ->visible(canDo('store', 'export'))
                ,
                ImportAction::make()
                    ->importer(ProductOptionValueImporter::class)
                // ->visible(canDo('store', 'import'))
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
            'index' => ManageProductOptionValues::route('/'),
        ];
    }
}
