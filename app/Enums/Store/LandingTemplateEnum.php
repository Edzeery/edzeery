<?php

namespace App\Enums\Store;

enum LandingTemplateEnum: string
{
    case SINGLE_PRODUCT = 'single_product';
    case CATALOG        = 'catalog';
    case BRAND          = 'brand';

    public function label(): string
    {
        return match ($this) {
            self::SINGLE_PRODUCT => 'Single Product',
            self::CATALOG        => 'Catalog',
            self::BRAND          => 'Brand Store',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::SINGLE_PRODUCT => 'Perfect for stores selling one hero product with a focused landing page.',
            self::CATALOG        => 'Full product catalog with categories and search.',
            self::BRAND          => 'Brand-focused storefront with product collections.',
        };
    }

    public static function options(): array
    {
        return array_map(fn (self $case) => [
            'value'       => $case->value,
            'label'       => $case->label(),
            'description' => $case->description(),
        ], self::cases());
    }
}
