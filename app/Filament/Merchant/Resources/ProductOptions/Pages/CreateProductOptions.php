<?php

namespace App\Filament\Merchant\Resources\ProductOptions\Pages;

use App\Filament\Merchant\Resources\ProductOptions\ProductOptionsResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProductOptions extends CreateRecord
{
    protected static string $resource = ProductOptionsResource::class;


    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('edit', [
            'record' => $this->record,
        ]);
    }
}
