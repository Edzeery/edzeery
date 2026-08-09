<?php
 
use Illuminate\Support\Facades\Route;

Route::domain('{store:slug}.' . config('app.domain'))
    ->middleware(['web', 'resolve.store'])
    ->group(function () {

        // Route::get('/', [StorefrontController::class, 'home']);
        // Route::get('/products', [StorefrontController::class, 'home']);
        // Route::get('/product/{slug}', [StorefrontController::class, 'home']);

    });
