<?php

namespace App\Domains\Account\DTOs;

class PasswordData
{
    public function __construct(
        public readonly string $current_password,
        public readonly string $new_password,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            $data['current_password'],
            $data['new_password'],
        );
    }
}
