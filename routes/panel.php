<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

/*
|--------------------------------------------------------------------------
| Custom admin panel (Volt)
|--------------------------------------------------------------------------
| The dedicated Edzeery dashboard built with Livewire 3 + Volt.
| Every page here is a functional component under resources/views/livewire/.
*/

Route::prefix('panel')
    ->middleware(['auth', 'verified'])
    ->name('panel.')
    ->group(function (): void {

        Volt::route('/', 'panel.dashboard')->name('dashboard');
        Volt::route('/settings', 'panel.settings')->name('settings');
    });
