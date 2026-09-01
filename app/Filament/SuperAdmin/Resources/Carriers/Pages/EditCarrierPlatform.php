<?php

namespace App\Filament\SuperAdmin\Resources\Carriers\Pages;

use App\Filament\SuperAdmin\Resources\Carriers\CarrierPlatformResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCarrierPlatform extends EditRecord
{
    protected static string $resource = CarrierPlatformResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
