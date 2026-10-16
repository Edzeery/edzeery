<?php

namespace App\Domains\Account\DTOs;

use Illuminate\Http\UploadedFile;

class ProfileData
{
    public function __construct(
        public readonly ?string $full_name,
        public readonly ?string $phone,
        public readonly ?string $address,
        public readonly ?string $birthdate,
        public readonly UploadedFile|string|null $profile_picture,
        public readonly ?string $country_id,
        public readonly ?string $state_id,
        public readonly ?string $city_id,
        public readonly ?string $name = null,
        public readonly ?string $email = null,
        public readonly bool $remove_avatar = false,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            $data['full_name'] ?? $data['name'] ?? null,
            $data['phone'] ?? null,
            $data['address'] ?? null,
            $data['birthdate'] ?? null,
            $data['profile_picture'] ?? null,
            $data['country_id'] ?? null,
            $data['state_id'] ?? null,
            $data['city_id'] ?? null,
            $data['name'] ?? null,
            $data['email'] ?? null,
            (bool) ($data['remove_avatar'] ?? false),
        );
    }
}
