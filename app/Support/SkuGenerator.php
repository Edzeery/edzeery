<?php

namespace App\Support;

use Illuminate\Support\Str;

class SkuGenerator
{
    /**
     * Normalize any SKU part
     */
    public static function normalizePart(string $value): string
    {
        return Str::of($value)
            ->lower()
            ->replace(' ', '-')
            ->replaceMatches('/[^a-z0-9\-]/', '')
            ->toString();
    }

    /**
     * Normalize full SKU
     */
    protected static function normalize(string $sku): string
    {
        return strtoupper(
            Str::of($sku)
                ->replaceMatches('/\-+/', '-')
                ->trim('-')
        );
    }

    /**
     * Base Product SKU
     * STORE-SLUG-PRODUCT
     */
    public static function product(string $storeSlug, string $productSlug): string
    {
        return self::normalize(
            self::normalizePart($storeSlug)
            . '-' .
            self::normalizePart($productSlug)
        );
    }

    /**
     * Variant SKU
     * STORE-SLUG-PRODUCT-OPTION1-OPTION2
     */
    public static function variant(
        string $storeSlug,
        string $productSlug,
        array $optionValues
    ): string {
        $parts = collect($optionValues)
            ->filter()
            ->map(fn ($v) => self::normalizePart($v))
            ->toArray();

        return self::normalize(
            self::normalizePart($storeSlug)
            . '-' .
            self::normalizePart($productSlug)
            . (!empty($parts) ? '-' . implode('-', $parts) : '')
        );
    }
}
