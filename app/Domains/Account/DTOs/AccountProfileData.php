<?php

namespace App\Domains\Account\DTOs;

use App\Enums\Platform\UserRoleEnum;
use App\Enums\Store\StoreRoleEnum;

class AccountProfileData
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $fullName,
        public readonly string $email,
        public readonly ?string $phone,
        public readonly ?string $address,
        public readonly ?string $birthdate,
        public readonly ?string $avatar,
        
        public readonly UserRoleEnum|StoreRoleEnum $membershipRole,

        public readonly ?string $country,
        public readonly ?string $state,
        public readonly ?string $city,

        public readonly ?string $language,
        public readonly ?string $theme,
    ) {}

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
