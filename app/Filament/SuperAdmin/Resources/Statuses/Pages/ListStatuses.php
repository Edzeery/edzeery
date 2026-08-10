<?php

namespace App\Filament\SuperAdmin\Resources\Statuses\Pages;

use App\Filament\SuperAdmin\Resources\Statuses\StatusResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStatuses extends ListRecords
{
    protected static string $resource = StatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
