<?php

namespace App\Filament\Merchant\Resources\Brands\Pages;

use App\Filament\Merchant\Resources\Brands\BrandResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;

class ManageBrands extends ManageRecords
{
    protected static string $resource = BrandResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        Notification::make()
            ->title('Saved successfully')
            ->success()
            ->body('band is added successfully.')
            ->send();
        return parent::mutateFormDataBeforeSave($data);
    }


    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }


    protected function getCreatedNotificationMessage(): ?string
    {
        return 'Brand created successfully';
    }
}
