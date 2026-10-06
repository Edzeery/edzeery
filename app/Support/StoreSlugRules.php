<?php

namespace App\Support;

use Illuminate\Validation\ValidationException;

/**
 * Single source of truth for store slugs reserved by the platform.
 *
 * A store registered under one of these labels would shadow (or be shadowed
 * by) platform subdomain routes: the "{store}.{domain}" storefront pattern is
 * registered before the "www" landing redirect, so a "www" store would win
 * every "www.*" request. The same guard protects "api", "app", "mail" and the
 * rest from colliding with platform-owned subdomains.
 *
 * Both enforcement points read from here — the Filament SuperAdmin StoreForm
 * slug rule and the Store model's saving guard — so the list can never drift
 * between UI validation and the model boundary.
 *
 * The single exemption is Store::withReservedSlug(), used ONLY by
 * DemoStoreSeeder so the platform's own demo store can live at "demo".
 * Nothing else may create or rename a store onto a reserved slug.
 */
class StoreSlugRules
{
    public const RESERVED_SLUGS = [
        'www', 'api', 'admin', 'mail', 'app', 'demo',
        'edzeery', 'support', 'help', 'status', 'cdn', 'assets',
    ];

    public static function reservedSlugs(): array
    {
        return self::RESERVED_SLUGS;
    }

    public static function isReserved(?string $slug): bool
    {
        return in_array(strtolower(trim((string) $slug)), self::RESERVED_SLUGS, true);
    }

    public static function exception(string $slug): ValidationException
    {
        return ValidationException::withMessages([
            'slug' => __('validation.reserved', ['attribute' => 'slug']),
        ]);
    }
}