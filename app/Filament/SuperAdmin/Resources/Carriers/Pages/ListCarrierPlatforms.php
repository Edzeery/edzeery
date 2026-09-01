<?php

namespace App\Filament\SuperAdmin\Resources\Carriers\Pages;

use App\Filament\SuperAdmin\Resources\Carriers\CarrierPlatformResource;
use Filament\Resources\Pages\ListRecords;

class ListCarrierPlatforms extends ListRecords
{
    protected static string $resource = CarrierPlatformResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
