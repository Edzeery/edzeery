<?php

namespace App\Domains\Account\Actions\Settings;

use App\Models\User;
use App\Domains\Account\DTOs\SettingsData;

class UpdateSettingsAction
{
    public function execute(User $user, SettingsData $data): void
    {
        $user->settings()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'preferences' => array_filter([
                    'language' => $data->language,
                    'theme' => $data->theme,
                ]),
            ]
        );
    }
}
