<?php

namespace App\Support\Loading;

/**
 * Per-request registry of Livewire actions opted into the global loading
 * overlay. Pages opt in declaratively via `<x-edz.loading-target />`; the
 * root panel layout serialises the collection for the `edzLoader` Alpine
 * component, which shows the overlay only while a registered action is
 * actually in flight.
 *
 * The static store is intentionally per-request: PHP-FPM gives each request
 * a fresh process, and on wire:navigate the server renders only the target
 * page, so the registry always reflects exactly the current page's actions.
 */
class LoadingActions
{
    /** @var array<string, string|null> map of Livewire method => optional label */
    protected static array $actions = [];

    public static function register(string $method, ?string $label = null): void
    {
        static::$actions[$method] = $label;
    }

    public static function all(): array
    {
        return static::$actions;
    }
}