<?php

namespace App\Filament\SuperAdmin\Resources\Carriers\Pages;

use App\Filament\SuperAdmin\Resources\Carriers\CarrierResource;
use Filament\Resources\Pages\ListRecords;

class ListCarriers extends ListRecords
{
    protected static string $resource = CarrierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
