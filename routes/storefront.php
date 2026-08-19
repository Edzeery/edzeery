<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::domain('{store:slug}.' . config('app.domain'))
    ->middleware(['web', 'resolve.store'])
    ->group(function () {

        // Storefront Home
        Volt::route('/', 'storefront.home')->name('storefront.home');

        // Checkout
        Volt::route('/checkout', 'storefront.order-form')->name('storefront.checkout');

        // Cart AJAX endpoints
        Route::post('/cart/add', [\App\Http\Controllers\Storefront\CartController::class, 'add'])->name('storefront.cart.add');
        Route::post('/cart/update', [\App\Http\Controllers\Storefront\CartController::class, 'update'])->name('storefront.cart.update');
        Route::post('/cart/remove', [\App\Http\Controllers\Storefront\CartController::class, 'remove'])->name('storefront.cart.remove');
        Route::get('/cart/count', [\App\Http\Controllers\Storefront\CartController::class, 'count'])->name('storefront.cart.count');

        // Order success
        Volt::route('/order/success/{order}', 'storefront.order-success')->name('storefront.order.success');

        // Product detail
        Volt::route('/product/{product:slug}', 'storefront.product-detail')->name('storefront.product');

        // Language switcher
        Route::get('/lang/{locale}', function (string $locale) {
            $allowed = ['ar', 'fr', 'en', 'es'];
            if (in_array($locale, $allowed)) {
                session(['locale' => $locale]);
                setcookie('lang', $locale, time() + (365 * 24 * 60 * 60), '/');
                app()->setLocale($locale);
            }
            return redirect()->back();
        })->name('storefront.lang');
    });
