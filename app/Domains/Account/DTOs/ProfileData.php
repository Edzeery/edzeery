<?php

namespace App\Domains\Account\DTOs;

class ProfileData
{
    public function __construct(
        public readonly ?string $full_name,
        public readonly ?string $phone,
        public readonly ?string $address,
        public readonly ?string $birthdate,
        public readonly ?string $profile_picture,
        public readonly ?string $country_id,
        public readonly ?string $state_id,
        public readonly ?string $city_id,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            $data['full_name'] ?? null,
            $data['phone'] ?? null,
            $data['address'] ?? null,
            $data['birthdate'] ?? null,
            $data['profile_picture'] ?? null,
            $data['country_id'] ?? null,
            $data['state_id'] ?? null,
            $data['city_id'] ?? null,
        );
    }
}
