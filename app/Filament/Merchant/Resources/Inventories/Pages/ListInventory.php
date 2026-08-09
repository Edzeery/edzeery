<?php

namespace App\Filament\Merchant\Resources\Inventories\Pages;

use App\Filament\Merchant\Resources\Inventories\InventoryResource;
use Filament\Resources\Pages\ListRecords;

class ListInventory extends ListRecords
{
    protected static string $resource = InventoryResource::class;
    protected function getHeaderWidgets(): array
    {
        return InventoryResource::getWidgets();
    }
}
