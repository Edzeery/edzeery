<?php

namespace App\Filament\Resources\Locations\Cities\Pages;

use App\Filament\Resources\Locations\Cities\CityResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageCities extends ManageRecords
{
    protected static string $resource = CityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
