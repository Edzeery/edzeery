<?php

namespace App\Filament\Merchant\Resources\ProductOptions\Pages;

use App\Filament\Merchant\Resources\ProductOptions\ProductOptionsResource;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditProductOptions extends EditRecord
{
    protected static string $resource = ProductOptionsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->disabled(fn() => $this->record->isUsedInVariants())
                ->before(function () {
                    if ($this->record->isUsedInVariants()) {
                        Notification::make()
                            ->danger()
                            ->title('Cannot delete option')
                            ->body('This option is used in product variants.')
                            ->send();

                        $this->halt();
                    }
                }),
        ];
    }
}
