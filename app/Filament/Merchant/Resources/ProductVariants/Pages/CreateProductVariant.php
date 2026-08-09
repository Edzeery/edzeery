<?php

namespace App\Filament\Merchant\Resources\ProductVariants\Pages;

use App\Filament\Merchant\Resources\ProductVariants\ProductVariantResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProductVariant extends CreateRecord
{
    protected static string $resource = ProductVariantResource::class;
}
