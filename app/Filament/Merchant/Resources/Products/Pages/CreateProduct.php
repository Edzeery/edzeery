<?php

namespace App\Filament\Merchant\Resources\Products\Pages;

use App\Actions\Product\CreateProductAction;
use App\Filament\Merchant\Resources\Products\ProductResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function getFormActions(): array
    {
        return [];
    }

    protected function handleRecordCreation(array $data): Model
    {

        return app(CreateProductAction::class)->handle(currentStore(), $data);
    }

    protected function getCreatedNotificationMessage(): ?string
    {
        return 'Product created successfully';
    }
}
