<?php

namespace App\Filament\SuperAdmin\Resources\Carriers\Pages;

use App\Filament\SuperAdmin\Resources\Carriers\CarrierResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCarrier extends EditRecord
{
    protected static string $resource = CarrierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}