<?php

namespace App\Services;

use Illuminate\Support\Str;

class BarcodeService
{
    /**
     * Normalize barcode
     */
    public static function normalize(string $barcode): string
    {
        return Str::of($barcode)
            ->upper()
            ->replace(' ', '')
            ->replaceMatches('/[^A-Z0-9]/', '')
            ->toString();
    }

    /**
     * Generate random barcode
     * Default: CODE-128 compatible
     */
    public static function generate(
        int $length = 12,
        ?string $prefix = null
    ): string {
        $random = strtoupper(Str::random($length));

        return self::normalize(
            $prefix
                ? $prefix . $random
                : $random
        );
    }

    /**
     * Product barcode
     */
    public static function product(?string $manual = null): string
    {
        return $manual
            ? self::normalize($manual)
            : self::generate(10, 'P');
    }

    /**
     * Variant barcode
     */
    public static function variant(?string $manual = null): string
    {
        return $manual
            ? self::normalize($manual)
            : self::generate(12, 'V');
    }
}
