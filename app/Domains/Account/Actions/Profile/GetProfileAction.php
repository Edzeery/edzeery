<?php

namespace App\Domains\Account\Actions\Profile;

use App\Domains\Account\DTOs\AccountProfileData;
use App\Enums\Platform\UserRoleEnum;
use App\Enums\Store\StoreRoleEnum;
use App\Models\User;

class GetProfileAction
{
    public function execute(User $user): AccountProfileData
    {
        $user->load([
            'profile',
            'settings',
            'country',
            'state',
            'city',
            'merchantRole',
        ]);

        $roleName = $user->merchantRole->first()?->name;
        $membershipRole = null;

        if ($roleName) {
            $membershipRole = UserRoleEnum::tryFrom($roleName)
                ?? StoreRoleEnum::tryFrom($roleName);
        }

        $avatar = $user->profile?->avatarUrl() ?? asset('img/icons/noimg.png');

        return new AccountProfileData(
            membershipRole: $membershipRole,
            name: $user->name,
            fullName: $user->profile?->full_name,
            email: $user->email,
            phone: $user->profile?->phone,
            address: $user->profile?->address,
            birthdate: $user->profile?->birthdate,
            avatar: $avatar,

            country: $user->country?->code,
            state: $user->state?->name,
            city: $user->city?->name,

            language: $user->settings?->preferences['language'] ?? 'ar',
            theme: $user->settings?->preferences['theme'] ?? 'light',

            country_id: $user->country_id,
            state_id: $user->state_id,
            city_id: $user->city_id,
            avatarPath: $user->profile?->profile_picture,
        );
    }
}
