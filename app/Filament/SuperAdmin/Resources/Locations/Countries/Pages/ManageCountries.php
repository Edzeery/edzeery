<?php

namespace App\Filament\SuperAdmin\Resources\Locations\Countries\Pages;

use App\Filament\SuperAdmin\Resources\Locations\Countries\CountryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageCountries extends ManageRecords
{
    protected static string $resource = CountryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
