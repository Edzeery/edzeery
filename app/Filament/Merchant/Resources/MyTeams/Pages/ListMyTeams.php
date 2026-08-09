<?php

namespace App\Filament\Merchant\Resources\MyTeams\Pages;

use App\Filament\Merchant\Resources\MyTeams\MyTeamResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords; 

class ListMyTeams extends ListRecords
{
    protected static string $resource = MyTeamResource::class;
    public ?\App\Models\Stores\Store $store = null;


    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
