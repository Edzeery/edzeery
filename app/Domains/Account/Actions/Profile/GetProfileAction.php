<?php

namespace App\Domains\Account\Actions\Profile;

use App\Models\User;
use App\Domains\Account\DTOs\AccountProfileData;
use App\Enums\Platform\UserRoleEnum;
use App\Enums\Store\StoreRoleEnum;

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
            'merchantRole'
        ]);

        $roleName = $user->merchantRole->first()?->name;
        $membershipRole = null;

        if ($roleName) {
            $membershipRole = UserRoleEnum::tryFrom($roleName)
                ?? StoreRoleEnum::tryFrom($roleName);
        }


        return new AccountProfileData(
            membershipRole: $membershipRole,
            name: $user->name,
            fullName: $user->profile?->full_name,
            email: $user->email,
            phone: $user->profile?->phone,
            address: $user->profile?->address,
            birthdate: $user->profile?->birthdate,
            avatar: $user->profile?->profile_picture
                ? asset('storage/' . $user->profile->profile_picture)
                : asset('/storage/img/icons/noimg.png'),

            country: $user->country?->code,
            state: $user->state?->name,
            city: $user->city?->name,

            language: $user->settings?->preferences['language'] ?? 'ar',
            theme: $user->settings?->preferences['theme'] ?? 'light',
        );
    }
}
