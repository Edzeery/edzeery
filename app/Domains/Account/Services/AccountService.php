<?php

namespace App\Domains\Account\Services;

use App\Models\User;
use App\Domains\Account\DTOs\ProfileData;
use App\Domains\Account\DTOs\SettingsData;
use App\Domains\Account\DTOs\PasswordData;

use App\Domains\Account\Actions\Profile\UpdateProfileAction;
use App\Domains\Account\Actions\Settings\UpdateSettingsAction;
use App\Domains\Account\Actions\Security\UpdatePasswordAction;

class AccountService
{
    public function __construct(
        protected UpdateProfileAction $updateProfile,
        protected UpdateSettingsAction $updateSettings,
        protected UpdatePasswordAction $updatePassword,
    ) {}

    public function updateProfile(User $user, ProfileData $data)
    {
        $this->updateProfile->execute($user, $data);
    }

    public function updateSettings(User $user, SettingsData $data)
    {
        $this->updateSettings->execute($user, $data);
    }

    public function updatePassword(User $user, PasswordData $data)
    {
        $this->updatePassword->execute($user, $data);
    }
}
