<?php
namespace App\Domains\Account\DTOs;

class SettingsData
{
    public function __construct(
        public readonly ?string $language,
        public readonly ?string $theme,
        public readonly ?string $timezone,
        public readonly ?string $date_format,
        public readonly ?bool $email_notifications,
        public readonly ?bool $order_notifications,
        public readonly ?bool $stock_notifications,
        public readonly ?bool $marketing_notifications,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            language: $data['language'] ?? null,
            theme: $data['theme'] ?? null,
            timezone: $data['timezone'] ?? null,
            date_format: $data['date_format'] ?? null,
            email_notifications: $data['email_notifications'] ?? null,
            order_notifications: $data['order_notifications'] ?? null,
            stock_notifications: $data['stock_notifications'] ?? null,
            marketing_notifications: $data['marketing_notifications'] ?? null,
        );
    }
}
