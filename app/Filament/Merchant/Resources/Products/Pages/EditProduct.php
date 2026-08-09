<?php

namespace App\Filament\Merchant\Resources\Products\Pages;

use App\Filament\Merchant\Resources\Products\ProductResource;
use App\Services\ProductService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getFormActions(): array
    {
        return [];
    }


    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(ProductService::class)->update($record, $data);
    }

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return array_merge(
            $data,
            app(\App\Services\ProductService::class)
                ->buildEditFormData($this->record)
        );
    }

    protected function getSavedNotificationMessage(): ?string
    {
                return 'Product saved successfully.';
    }

}
