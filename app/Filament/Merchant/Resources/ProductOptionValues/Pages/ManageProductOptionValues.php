<?php

namespace App\Filament\Merchant\Resources\ProductOptionValues\Pages;

use App\Filament\Merchant\Resources\ProductOptionValues\ProductOptionValueResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageProductOptionValues extends ManageRecords
{
    protected static string $resource = ProductOptionValueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
