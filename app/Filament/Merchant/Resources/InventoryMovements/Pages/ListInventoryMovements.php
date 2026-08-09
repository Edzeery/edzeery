<?php

namespace App\Filament\Merchant\Resources\InventoryMovements\Pages;

use App\Filament\Merchant\Resources\InventoryMovements\InventoryMovementResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListInventoryMovements extends ListRecords
{
    protected static string $resource = InventoryMovementResource::class;

}
