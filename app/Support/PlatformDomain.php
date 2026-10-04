<?php

namespace App\Support;

class PlatformDomain
{
    public static function base(?string $url = null): string
    {
        $appUrl = $url ?? config('app.url') ?? '';
        if (! $appUrl) {
            return config('app.domain') ?? '';
        }

        $parsed = parse_url($appUrl);
        $host = $parsed['host'] ?? '';
        if (! $host) {
            return config('app.domain') ?? '';
        }

        $parts = explode('.', $host);
        if (count($parts) > 2) {
            array_shift($parts);
        }

        return implode('.', $parts);
    }
}
