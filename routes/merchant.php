<?php

use App\Http\Controllers\Account\ProfileController;
use App\Http\Controllers\Account\SecurityController;
use App\Http\Controllers\Account\SettingsController;
use App\Http\Middleware\Merchant\Store\EnsureHasStoreRole;
use App\Http\Middleware\Merchant\Store\EnsureStoreIsActive;
use App\Http\Middleware\Merchant\Store\EnsureStoreMembership;
use App\Http\Middleware\Merchant\Store\EnsureStoreResolved;
use App\Http\Middleware\Merchant\Store\ResolveStoreFromRoute;
use App\Http\Controllers\Merchant\DashboardController;
use App\Http\Controllers\Merchant\StoreController;
use App\Http\Controllers\Merchant\BillingController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::prefix('account')
    ->middleware(['auth', 'verified']) // تأكد أن المستخدم مسجل دخول والتحقق من البريد
    ->name('account.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('merchant.dashboard');


        // Stores
        Route::get('/stores', [StoreController::class, 'index'])->name('stores');
        Route::get('/stores/create', [StoreController::class, 'create'])->name('stores.create');
        Route::get('/stores/{store}/edit', [StoreController::class, 'edit'])->name('stores.edit');

        // Billing / Subscriptions
        Route::get('/billing', [BillingController::class, 'index'])->name('billing');

        // Route::get('/profile', function () {
        //     return view('pages.profile', ['title' => 'Profile']);
        // })->name('profile');
        // // Profile


        Route::get('/profile', [ProfileController::class, 'index'])
            ->name('index');

        Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');

        Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');

        Route::post('/password', [SecurityController::class, 'updatePassword'])->name('password.update');
    });

/*
|--------------------------------------------------------------------------
| Livewire merchant panel (Volt)
|--------------------------------------------------------------------------
| The store-scoped merchant dashboard built with Livewire 3 + Volt.
| Routes are keyed by the store slug ({store:slug}) and reuse the same
| store-context middleware chain as the legacy Filament panel so that
| membership, role and store-status checks stay enforced identically.
*/

Route::prefix('merchant')
    ->middleware(['auth', 'verified'])
    ->name('merchant.')
    ->group(function (): void {
        Volt::route('/create-store', 'merchant.create-store')->name('create-store');
    });

Route::prefix('merchant')
    ->middleware([
        'auth',
        'verified',
        ResolveStoreFromRoute::class,
        EnsureStoreResolved::class,
        EnsureStoreMembership::class,
        EnsureHasStoreRole::class.':owner,admin,manager,staff',
        EnsureStoreIsActive::class,
    ])
    ->name('merchant.')
    ->group(function (): void {
        Volt::route('/{store:slug}/products', 'merchant.products.index')->name('products.index');
        Volt::route('/{store:slug}/products/create', 'merchant.products.form')->name('products.create');
        Volt::route('/{store:slug}/products/{product}/edit', 'merchant.products.form')->name('products.edit');
        Volt::route('/{store:slug}/products/{product}', 'merchant.products.show')->name('products.show');
        Volt::route('/{store:slug}/brands', 'merchant.brands.index')->name('brands.index');
        Volt::route('/{store:slug}/categories', 'merchant.categories.index')->name('categories.index');
        Volt::route('/{store:slug}/options', 'merchant.options.index')->name('options.index');
        Volt::route('/{store:slug}/variants', 'merchant.variants.index')->name('variants.index');
        Volt::route('/{store:slug}/inventories', 'merchant.inventories.index')->name('inventories.index');
        Volt::route('/{store:slug}/inventory-movements', 'merchant.inventory-movements.index')->name('inventory-movements.index');
        Volt::route('/{store:slug}/stock-alerts', 'merchant.stock-alerts.index')->name('stock-alerts.index');
        Volt::route('/{store:slug}/teams', 'merchant.teams.index')->name('teams.index');
    });
