<?php

namespace App\Filament\Merchant\Resources\ProductOptions\Pages;

use App\Filament\Merchant\Resources\ProductOptions\ProductOptionsResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListProductOptions extends ListRecords
{
    protected static string $resource = ProductOptionsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('Option Values')
                ->icon(Heroicon::OutlinedListBullet)
                ->tooltip(__('messages.values_tooltip'))
                ->label(__('titles.values'))
                ->link()

                ->url(fn() => route('filament.merchant.products.resources.product-option-values.index', currentStore())),
        ];
    }
}
