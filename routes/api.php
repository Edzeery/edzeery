<?php

use App\Http\Controllers\Api\V1\ProductsController;
use App\Http\Controllers\Api\Webhooks\ChargilyWebhookController;
use App\Http\Controllers\Api\Webhooks\DeliveryWebhookController;
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

/*
|--------------------------------------------------------------------------
| Payment Webhooks
|--------------------------------------------------------------------------
| Public endpoint called by Chargily Pay. Signature is verified against
| the raw payload using HMAC-SHA256 inside the gateway.
*/

Route::post('/webhooks/chargily', ChargilyWebhookController::class)
    ->name('webhooks.chargily')
    ->middleware('throttle:60,1');

/*
|--------------------------------------------------------------------------
| Delivery Webhooks
|--------------------------------------------------------------------------
| Public endpoint fed by carrier delivery-tracking push events. Each carrier
| gets a stable per-domain, per-provider URL (/webhooks/delivery/{provider}
| where {provider} is the shipping_provider.code, e.g. "noest"). The store is
| identified by its secret token, sent via the X-Delivery-Token header with a
| ?token= query fallback. The controller reuses NoestTrackingSyncService::apply
| so pushes behave exactly like scheduled polls. The legacy token-in-path form
| (the segment was the secret itself) keeps working transiently.
*/

Route::post('/webhooks/delivery/{provider}', DeliveryWebhookController::class)
    ->name('webhooks.delivery')
    ->middleware('throttle:120,1');
