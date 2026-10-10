<?php

use App\Domains\Cart\Support\OrderRules;
use App\Http\Middleware\ResolveStoreContextFromHost;
use App\Models\Products\Product;
use App\Models\Products\ProductVariant;
use App\Models\Stores\Store;
use App\Support\StoreContext;
use Illuminate\Http\Request;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

/**
 * Root-cause regression for the storefront product-page crash:
 *
 *   OrderRules::limits(): Argument #1 ($product) must be of type Product, null
 *   given (called from OrderRules.php:73)
 *
 * product-detail mounts with variants WITHOUT the product relation, so a wire
 * update rehydrates the component and lazy-loads variant->product. Livewire
 * updates POST to /livewire/update, outside the {store}.{domain} route group,
 * so no store context was present and StoreScope failed closed -> product null.
 *
 * The fix has two layers which these tests lock together:
 *   1. ResolveStoreContextFromHost (global web middleware) restores the tenant
 *      from the host on EVERY web request, including /livewire/update.
 *   2. product-detail / variant-matrix now pass the mounted product + store
 *      into OrderRules::lineCap instead of relying on the lazy relation.
 */
afterEach(function () {
    app(StoreContext::class)->clear();
});

function capContextStore(array $overrides = []): Store
{
    $user = \App\Models\User::factory()->create();

    $store = Store::create(array_merge([
        'user_id' => $user->id,
        'name' => 'Cap Context Store',
        'slug' => 'ctx-'.uniqid(),
        'status' => 'active',
    ], $overrides));

    config(['app.domain' => 'example.test']);

    return $store;
}

function capContextProduct(Store $store): Product
{
    return Product::create([
        'store_id' => $store->id,
        'name' => 'Context Product',
        'slug' => 'ctx-p-'.uniqid(),
        'sku' => 'CTX-'.uniqid(),
        'type' => 'simple',
        'price' => 2500,
        'is_active' => true,
    ]);
}

function capContextVariant(Store $store, Product $product): ProductVariant
{
    return ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Default',
        'sku' => 'CTX-V-'.uniqid(),
        'price' => 2500,
        'stock' => 5,
    ]);
}

it('adds to cart and increments quantity on the product page without crashing', function () {
    $store = capContextStore();
    $product = capContextProduct($store)->load(['variants', 'store']);
    capContextVariant($store, $product);

    $product = $product->refresh()->load(['variants', 'store']);

    app(StoreContext::class)->set($store);
    config(['app.domain' => 'example.test']);

    \Livewire\Volt\Volt::test('storefront.product-detail', ['product' => $product])
        ->call('incrementQuantity')
        ->call('incrementQuantity')
        ->assertSet('quantity', fn (int $q) => $q >= 2)
        ->call('addToCart')
        ->assertSet('quantity', fn (int $q) => $q >= 2);

    expect(app(StoreContext::class)->id())->toBe($store->id);
});

it('evaluates the per-line cap from the mounted product even with no store context', function () {
    $store = capContextStore();
    $product = capContextProduct($store);
    $variant = capContextVariant($store, $product);

    // Mirrors what a storefront wire update sees: the product relation is NOT
    // part of the snapshot, so variant->product must never be queried.
    $product = $product->load('variants');

    app(StoreContext::class)->clear();

    $cap = OrderRules::lineCap($variant, $product->store, $product);

    expect($variant->relationLoaded('product'))->toBeFalse()
        ->and($cap)->toBeInt()
        ->and($cap)->toBeGreaterThanOrEqual(1);
});

it('restores the storefront tenant from the request host when context is empty', function () {
    $store = capContextStore();

    app(StoreContext::class)->clear();

    $request = Request::create('http://'.$store->slug.'.example.test/product/'.uniqid(), 'GET');

    (new ResolveStoreContextFromHost)->handle($request, fn () => response('ok'));

    expect(app(StoreContext::class)->id())->toBe($store->id);
});

it('never fabricates context for reserved hosts, unknown slugs or inactive stores', function () {
    capContextStore(['slug' => 'live-store', 'status' => 'active']);
    capContextStore(['slug' => 'dormant-store', 'status' => 'closed']);

    $middleware = new ResolveStoreContextFromHost;

    foreach (['www', 'app', 'admin', 'api', 'mail'] as $reserved) {
        app(StoreContext::class)->clear();

        $request = Request::create('http://'.$reserved.'.example.test/', 'GET');

        $middleware->handle($request, fn () => response('ok'));

        expect(app(StoreContext::class)->id())->toBeNull();
    }

    app(StoreContext::class)->clear();
    $unknown = Request::create('http://unknown-office.example.test/', 'GET');
    $middleware->handle($unknown, fn () => response('ok'));
    expect(app(StoreContext::class)->id())->toBeNull();

    app(StoreContext::class)->clear();
    $inactive = Request::create('http://dormant-store.example.test/', 'GET');
    $middleware->handle($inactive, fn () => response('ok'));
    expect(app(StoreContext::class)->id())->toBeNull();
});
