<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Volt\Volt;

class VoltServiceProvider extends ServiceProvider
{
    /**
     * Mount Volt functional-component directories.
     *
     * Every Blade file under resources/views/livewire/ becomes a
     * resolvable Volt component (unless a matching Livewire class
     * exists, which then takes precedence).
     */
    public function boot(): void
    {
        Volt::mount([
            resource_path('views/livewire'),
        ]);
    }
}
