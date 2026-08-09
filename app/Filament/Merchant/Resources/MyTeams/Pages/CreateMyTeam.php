<?php

namespace App\Filament\Merchant\Resources\MyTeams\Pages;

use App\Filament\Merchant\Resources\MyTeams\MyTeamResource;
use App\Models\Stores\Team\StoreMembership;
use App\Services\Stores\StoreTeamService;
use Filament\Resources\Pages\CreateRecord;

class CreateMyTeam extends CreateRecord
{
    protected static string $resource = MyTeamResource::class;


    protected function handleRecordCreation(array $data): StoreMembership
    {
        return app(StoreTeamService::class)
            ->addMember(currentStore(), $data);
    }

     protected function getCreatedNotificationMessage(): ?string
    {
        return __('messages.created_successfully');
    }


}
