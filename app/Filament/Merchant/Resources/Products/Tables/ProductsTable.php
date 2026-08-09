<?php

namespace App\Filament\Merchant\Resources\Products\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Grid;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn(Builder $query) =>
                $query->with([
                    'primaryImage',
                    'brand',
                    'primaryCategory',
                ])
            )

            ->columns([

                ImageColumn::make('PrimaryImagePath')
                    ->label(__('table.logo'))
                    ->disk('public')
                    ->circular()
                    ->default('/img/icons/noimg.png'),

                TextColumn::make('name')
                    ->label(__('table.name'))
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(fn($record) => 'BarCode : ' . $record->barcode),

                TextColumn::make('sku')
                    ->label(__('table.sku'))
                    ->searchable()
                    ->sortable()
                    ->fontFamily('mono')
                    ->toggleable(),
                TextColumn::make('has_variants')
                    ->label('Variants')
                    ->toggleable()
                    ->getStateUsing(fn($record) => $record->hasVariants())
                    ->formatStateUsing(fn($state) => $state ? 'Yes' : 'No'),


                TextColumn::make('brand.name')
                    ->label(__('table.brand'))
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('primaryCategory.name')
                    ->label(__('table.category'))
                    ->sortable()
                    ->toggleable(),

                ToggleColumn::make('is_active')
                    ->label(__('status.active'))
                    ->sortable()
                    ->onColor('success')
                    ->offColor('gray')
                    ->toggleable(),

                ToggleColumn::make('is_featured')
                    ->label(__('status.featured'))
                    ->sortable()
                    ->onColor('warning')
                    ->offColor('gray')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label(__('table.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])

            ->filters([

                Filter::make('type')
                    ->schema([
                        DatePicker::make('created_at')
                            ->label(__('table.created_at'))
                    ])
                    ->query(function ($query, $data) {
                        return $query->when($data['created_at'], function ($q, $date) {
                            $q->whereDate("created_at", $date);
                        });
                    }),


                SelectFilter::make('brand_id')
                    ->relationship('brand', 'name')
                    ->label(__('titles.brand'))
                    ->searchable()
                    ->preload(),



                // SelectFilter::make('primary_category_id')
                //     ->relationship('primaryCategory', 'name')
                //     ->label(__('titles.category'))
                //     ->searchable()
                //     ->preload(),



                TernaryFilter::make('is_active')
                    ->label(__('status.active')),

                TernaryFilter::make('is_featured')
                    ->label(__('status.featured')),

            ])

            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),

                    BulkAction::make('activate')
                        ->label(__('actions.activate'))
                        ->icon('heroicon-m-check')
                        ->action(
                            fn($records) =>
                            $records->each->update(['is_active' => true])
                        )
                        ->requiresConfirmation(),

                    BulkAction::make('deactivate')
                        ->label(__('actions.deactivate'))
                        ->icon('heroicon-m-x-mark')
                        ->action(
                            fn($records) =>
                            $records->each->update(['is_active' => false])
                        )
                        ->requiresConfirmation(),
                ]),
            ])

            ->emptyStateHeading(__('messages.no_data'))
            ->emptyStateActions([
                Action::make('create')
                    ->label(__('buttons.create') . ' ' . __('buttons.new'))
                    ->icon('heroicon-m-plus')
                    ->url(route('filament.merchant.products.resources.products.create', currentStore()))
                    ->button(),
            ]);
    }
}
