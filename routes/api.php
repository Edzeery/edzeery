<?php

use App\Http\Controllers\Api\V1\ProductsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| REST API
|--------------------------------------------------------------------------
| Versioned JSON API mounted under /api/v1.
| Authenticated with Sanctum tokens; every request must carry a store
| context (X-Store-Id header) unless the caller is a platform admin —
| enforced by the `store.context` middleware and the global StoreScope.
*/

Route::prefix('v1')
    ->middleware(['auth:sanctum', 'store.context'])
    ->group(function (): void {

        Route::get('/user', fn () => request()->user());

        // ---- Products domain ----
        Route::apiResource('products', ProductsController::class);
    });
