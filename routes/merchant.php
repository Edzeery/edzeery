<?php

use App\Http\Controllers\Account\SecurityController;
use App\Http\Controllers\Account\SettingsController;
use App\Http\Middleware\Merchant\Store\EnsureHasStoreRole;
use App\Http\Middleware\Merchant\Store\EnsureStoreIsActive;
use App\Http\Middleware\Merchant\Store\EnsureStoreMembership;
use App\Http\Middleware\Merchant\Store\EnsureStoreResolved;
use App\Http\Middleware\Merchant\Store\ResolveStoreFromRoute;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

/*
|--------------------------------------------------------------------------
| Layer 1: Account — user-level pages, independent of any store
*/
Route::prefix('merchant/account')
    ->middleware(['auth', 'verified'])
    ->name('account.')
    ->group(function () {

        Volt::route('/profile', 'account.profile')->name('profile');
        Volt::route('/billing', 'account.billing')->name('billing');

        Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
        Route::post('/password', [SecurityController::class, 'update'])->name('password.update');
    });

/*
|--------------------------------------------------------------------------
| Layer 2: Merchant — store-selection pages, no {store:slug}
*/
Route::prefix('merchant')
    ->middleware(['auth', 'verified'])
    ->name('merchant.')
    ->group(function (): void {
        Volt::route('/stores', 'merchant.stores.index')->name('stores.index');
        Volt::route('/create-store', 'merchant.create-store')->name('create-store');
    });

/*
|--------------------------------------------------------------------------
| Layer 3: Store — scoped to a single {store:slug}
*/
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
        Volt::route('/{store:slug}/dashboard', 'merchant.dashboard')->name('dashboard');

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
        Volt::route('/{store:slug}/orders', 'merchant.orders.index')->name('orders.index');
        Volt::route('/{store:slug}/storefront-settings', 'merchant.storefront-settings')->name('storefront-settings');
        Volt::route('/{store:slug}/settings', 'merchant.store-settings')->name('store-settings');

        // Finance / Debts
        Volt::route('/{store:slug}/debts', 'merchant.debts.index')->name('debts.index');
        Volt::route('/{store:slug}/debts/create', 'merchant.debts.form')->name('debts.create');
        Volt::route('/{store:slug}/debts/{debt}/edit', 'merchant.debts.form')->name('debts.edit');
        Volt::route('/{store:slug}/debts/{debt}', 'merchant.debts.show')->name('debts.show');
    });
