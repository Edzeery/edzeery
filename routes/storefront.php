<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::domain('{store:slug}.' . config('app.domain'))
    ->middleware(['web', 'resolve.store'])
    ->group(function () {

        // Storefront Home
        Volt::route('/', 'storefront.home')->name('home');

        // Checkout
        Volt::route('/checkout', 'storefront.order-form')->name('checkout');

        // Cart AJAX endpoints
        Route::post('/cart/add', [\App\Http\Controllers\Storefront\CartController::class, 'add'])->name('cart.add');
        Route::post('/cart/update', [\App\Http\Controllers\Storefront\CartController::class, 'update'])->name('cart.update');
        Route::post('/cart/remove', [\App\Http\Controllers\Storefront\CartController::class, 'remove'])->name('cart.remove');
        Route::get('/cart/count', [\App\Http\Controllers\Storefront\CartController::class, 'count'])->name('cart.count');

        // Order success
        Volt::route('/order/success/{order}', 'storefront.order-success')->name('order.success');

        // Product detail
        Volt::route('/product/{product:slug}', 'storefront.product-detail')->name('product');
    });
