<?php

namespace App\Filament\Merchant\Resources\InventoryMovements\Pages;

use App\Filament\Merchant\Resources\InventoryMovements\InventoryMovementResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewInventoryMovement extends ViewRecord
{
    protected static string $resource = InventoryMovementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
