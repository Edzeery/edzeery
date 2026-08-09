<?php

namespace App\Providers;

use App\View\Components\StatusBadge;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class StatusKitServiceProvider extends ServiceProvider
{
    public function register(): void
    {

    }

    public function boot(): void
    {


        // Publish Views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'status-kit');

        // Publish Lang
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'status-kit');

        // Register Blade Component
        Blade::component('status-badge', StatusBadge::class);
    }
}
