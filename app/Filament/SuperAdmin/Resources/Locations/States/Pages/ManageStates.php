<?php

namespace App\Filament\SuperAdmin\Resources\Locations\States\Pages;

use App\Filament\SuperAdmin\Resources\Locations\States\StateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageStates extends ManageRecords
{
    protected static string $resource = StateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
