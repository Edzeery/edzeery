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
                    'timezone' => $data->timezone,
                    'date_format' => $data->date_format,
                    'email_notifications' => $data->email_notifications,
                    'order_notifications' => $data->order_notifications,
                    'stock_notifications' => $data->stock_notifications,
                    'marketing_notifications' => $data->marketing_notifications,
                ], fn ($v) => $v !== null),
            ]
        );
    }
}
