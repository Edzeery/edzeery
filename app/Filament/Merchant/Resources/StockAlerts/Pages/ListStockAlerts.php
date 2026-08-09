<?php

namespace App\Filament\Merchant\Resources\StockAlerts\Pages;

use App\Filament\Merchant\Resources\StockAlerts\StockAlertResource;
use Filament\Resources\Pages\ListRecords;

class ListStockAlerts extends ListRecords
{
    protected static string $resource = StockAlertResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
