<?php

namespace App\Filament\SuperAdmin\Resources\Statuses\Pages;

use App\Filament\SuperAdmin\Resources\Statuses\StatusResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStatus extends EditRecord
{
    protected static string $resource = StatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
