<?php

namespace App\Filament\Merchant\Resources\ProductOptions\RelationManagers;

use App\Filament\Exports\ProductOptionValueExporter;
use App\Filament\Imports\ProductOptionValueImporter;
use App\Models\Products\ProductOptionValue;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ValuesRelationManager extends RelationManager
{
    protected static string $relationship = 'values';

    protected static ?string $recordTitleAttribute = 'value';

    /* =========================
     | FORM (v4)
     ========================= */

    public function form(Schema $form): schema
    {
        return $form->schema([
            TextInput::make('value')
                ->required()
                ->maxLength(100)
                ->label(
                    fn() =>
                    match (strtolower($this->ownerRecord?->name)) {
                        'color' => 'Color name (Red, Black)',
                        'size'  => 'Size (40, 42)',
                        default => 'Value',
                    }


                )->unique(
                    table: 'product_option_values',
                    column: 'value',
                    ignoreRecord: true,
                    modifyRuleUsing: fn($rule) =>
                    $rule->where(
                        'product_option_id',
                        $this->ownerRecord->id
                    )
                )

        ]);
    }

    /* =========================
     | TABLE (v4)
     ========================= */
    public function table(Table $table): Table
    {
        return $table
            ->columns([
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
            ->headerActions([
                CreateAction::make(),
                Action::make('generateSizes')
                    ->label('Generate Sizes (25-45)')
                    ->icon('heroicon-o-squares-plus')
                    ->visible(
                        fn() =>
                        strtolower($this->ownerRecord?->name) === 'size'
                    )
                    ->action(function () {
                        foreach (range(25, 45) as $size) {
                            $this->ownerRecord->addValue((string) $size);
                        }

                        Notification::make()
                            ->success()
                            ->title('Sizes generated successfully')
                            ->send();
                    }),
            ])->headerActions([
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
                DeleteAction::make()
                    ->disabled(
                        fn(ProductOptionValue $record) =>
                        $record->variants()->exists()
                    )
                    ->tooltip(fn(ProductOptionValue $record) =>
                    $record->variants()->exists() ? 'This value is used in variants' : 'This value is not used in variants'),

            ]);
    }
}
