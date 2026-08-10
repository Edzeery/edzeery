<?php

namespace App\Filament\SuperAdmin\Resources\Stores\Pages;

use App\Filament\SuperAdmin\Resources\Stores\StoreResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStores extends ListRecords
{
    protected static string $resource = StoreResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
