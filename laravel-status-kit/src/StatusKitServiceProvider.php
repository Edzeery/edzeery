<?php

namespace Edzeery\LaravelStatusKit;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Edzeery\LaravelStatusKit\View\Components\StatusBadge;

class StatusKitServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/icons.php', 'icons');
        $this->mergeConfigFrom(__DIR__.'/../config/status-colors.php', 'status-colors');
        $this->mergeConfigFrom(__DIR__.'/../config/math_finance_symbols.php', 'math_finance_symbols');
    }

    public function boot(): void
    {
        // Publish Config
        $this->publishes([
            __DIR__.'/../config/icons.php' => config_path('icons.php'),
            __DIR__.'/../config/status-colors.php' => config_path('status-colors.php'),
            __DIR__.'/../config/math_finance_symbols.php' => config_path('math_finance_symbols.php'),
        ], 'status-kit-config');

        // Publish Views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'status-kit');

        // Publish Lang
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'status-kit');

        // Register Blade Component
        Blade::component('status-badge', StatusBadge::class);
    }
}