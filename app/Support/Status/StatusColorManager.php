<?php

namespace App\Support\Status;

class StatusColorManager
{
    public static function get(string $group, string $status, bool $dark = false): string
    {
        $config = config("status-colors.{$group}.{$status}", []);
        return $config[$dark ? 'dark' : 'light'] ?? 'bg-gray-200 text-gray-600';
    }

    public static function filamentColor(string $group, string $status): string
    {
        return config("status-colors.{$group}.{$status}.filament") ?? 'primary';
    }

    public static function icon(string $group, string $status): ?string
    {
        return config("status-colors.{$group}.{$status}.icon") ?? null;
    }
}
