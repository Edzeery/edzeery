<?php

namespace App\Filament\Merchant\Resources\ProductOptions\Tables;

use App\Filament\Exports\ProductOptionExporter;
use App\Filament\Imports\ProductOptionImporter;
use App\Models\Products\ProductOption;
use App\Models\Products\ProductOptionValue;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductOptionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('values_count')
                    ->counts('values')
                    ->label('Values'),
            ])
            ->filters([
                Filter::make('type')
                    ->schema([
                        DatePicker::make('created_at')
                        ->label(__('general.created_at'))
                    ])
                    ->query( function ($query,$data) {

                        return $query->when($data['created_at'],function($q,$date){
                            $q->whereDate("created_at",$date);
                        });

                    })
            ])
            ->headerActions([
                ExportAction::make()
                    ->exporter(ProductOptionExporter::class)
                // ->visible(canDo('store', 'export'))
                ,
                ImportAction::make()
                    ->importer(ProductOptionImporter::class)
                // ->visible(canDo('store', 'import'))
            ])
            ->recordActions([
                EditAction::make(),
                ViewAction::make(),
                DeleteAction::make()
                    ->tooltip('If This value is used in variants , you can`t Deleted'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->before(function ($records) {
                            if ($records->contains(fn($r) => $r->isUsedInVariants())) {
                                throw new \RuntimeException(
                                    'Some options are used in product variants.'
                                );
                            }
                        }),
                ]),
            ]);
    }
}
