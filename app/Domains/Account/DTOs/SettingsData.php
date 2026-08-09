<?php
namespace App\Domains\Account\DTOs;

class SettingsData
{
    public function __construct(
        public readonly ?string $language,
        public readonly ?string $theme,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            language: $data['language'] ?? null,
            theme: $data['theme'] ?? null,
        );
    }
}
