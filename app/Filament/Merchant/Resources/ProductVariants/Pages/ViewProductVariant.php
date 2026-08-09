<?php

namespace App\Filament\Merchant\Resources\ProductVariants\Pages;

use App\Filament\Merchant\Resources\ProductVariants\ProductVariantResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewProductVariant extends ViewRecord
{
    protected static string $resource = ProductVariantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
