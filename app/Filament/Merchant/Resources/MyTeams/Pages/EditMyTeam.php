<?php

namespace App\Filament\Merchant\Resources\MyTeams\Pages;

use App\Filament\Merchant\Resources\MyTeams\MyTeamResource;
use App\Services\Stores\StoreTeamService;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditMyTeam extends EditRecord
{
    protected static string $resource = MyTeamResource::class;


    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(StoreTeamService::class)
            ->updateMember(currentStore(), $record, $data);
    }

    protected function getSavedNotificationMessage(): ?string
    {
        return __('messages.updated_successfully');
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
