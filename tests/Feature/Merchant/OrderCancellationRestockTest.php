<?php

use App\Domains\Order\Services\OrderService;
use App\Models\InventoryMovement;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use App\Models\Locations\City;
use App\Models\Orders\Order;
use App\Models\Products\Product;
use App\Models\Products\ProductVariant;
use App\Models\Stores\Store;
use Livewire\Volt\Volt;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

function orStore(array $settings = []): Store
{
    $user = \App\Models\User::factory()->create();

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Restock Store',
        'slug' => 'rst-' . uniqid(),
        'status' => 'active',
        'landing_template' => 'catalog',
    ]);

    if ($settings !== []) {
        $store->settings()->updateOrCreate([], $settings);
    }

    config(['app.domain' => 'example.test']);
    test()->withSession(['current_store_id' => $store->id]);

    return $store;
}

function orProduct(Store $store, string $skuPrefix): Product
{
    return Product::create([
        'store_id' => $store->id,
        'name' => 'Restock Product',
        'slug' => 'rpr-' . uniqid(),
        'sku' => $skuPrefix . '-' . uniqid(),
        'type' => 'variable',
        'price' => 500,
        'is_active' => true,
    ]);
}

function orVariant(Store $store, Product $product, int $stock): ProductVariant
{
    return ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Default',
        'sku' => 'RVR-' . uniqid(),
        'price' => 500,
        'stock' => $stock,
    ]);
}

function placeStopdeskOrder(Store $store, ProductVariant $variant, int $qty): Order
{
    $country = Country::create(['name' => 'RS Land', 'code' => substr(uniqid(), 0, 2), 'is_active' => true]);
    $state = State::create([
        'country_id' => $country->id,
        'state_code' => 'RS-01',
        'name' => 'RS State',
        'is_active' => true,
        'is_cod_available' => true,
    ]);
    $city = City::create([
        'state_id' => $state->id,
        'name' => 'RS City',
        'post_code' => '0000',
        'is_active' => true,
    ]);

    test()->artisan('view:clear');
    app(\App\Domains\Cart\Services\CartService::class)
        ->addItem($store->id, $variant->id, $qty);

    Volt::test('storefront.order-form')
        ->set('name', 'Cancel Target')
        ->set('phone', '0550000000')
        ->set('state_id', (string) $state->id)
        ->set('city_id', (string) $city->id)
        ->set('delivery_type', 'stopdesk')
        ->set('payment_method', 'cod')
        ->call('submitOrder');

    return Order::where('store_id', $store->id)->latest('id')->first();
}

test('cancelling a confirmed order restores stock with RELEASE movements once only', function () {
    test()->artisan('db:seed', ['--class' => \Database\Seeders\SystemStatusesSeeder::class, '--force' => true]);

    $store = orStore();
    $variant = orVariant($store, orProduct($store, 'CXL'), 6);

    $order = placeStopdeskOrder($store, $variant, 2);
    // pending → no inventory movement yet
    expect($order)->not->toBeNull()
        ->and($variant->fresh()->stock)->toBe(6);

    // confirmed → RESERVE (stock decreases)
    app(OrderService::class)->transition($order->fresh(), 'confirmed');
    expect($variant->fresh()->stock)->toBe(4);

    // cancelled → RELEASE (stock restored)
    app(OrderService::class)->transition($order->fresh(), 'cancelled');
    expect($variant->fresh()->stock)->toBe(6);

    $releases = InventoryMovement::where('product_variant_id', $variant->id)
        ->where('source_type', Order::class)
        ->where('source_id', $order->id)
        ->where('type', 'release')
        ->get();
    expect($releases)->toHaveCount(1)
        ->and((int) $releases[0]->quantity)->toBe(2)
        ->and((int) $releases[0]->balance_after)->toBe(6);

    // cancelled → pending → cancelled is legal; the guard must keep
    // the restock at exactly one movement (idempotency).
    app(OrderService::class)->transition($order->fresh(), 'pending');
    app(OrderService::class)->transition($order->fresh(), 'cancelled');

    expect(InventoryMovement::where('product_variant_id', $variant->id)
        ->where('source_id', $order->id)
        ->where('type', 'release')
        ->count())->toBe(1)
        ->and($variant->fresh()->stock)->toBe(6);
});

test('orders from stores without inventory tracking are never touched on cancel', function () {
    test()->artisan('db:seed', ['--class' => \Database\Seeders\SystemStatusesSeeder::class, '--force' => true]);

    $store = orStore(['inventory_tracking' => false]);
    $variant = orVariant($store, orProduct($store, 'OFF'), 9);

    $order = placeStopdeskOrder($store, $variant, 3);
    expect($variant->fresh()->stock)->toBe(9);

    // confirmed → no inventory change (tracking off)
    app(OrderService::class)->transition($order, 'confirmed');
    expect($variant->fresh()->stock)->toBe(9);

    app(OrderService::class)->transition($order->fresh(), 'cancelled');

    expect($variant->fresh()->stock)->toBe(9)
        ->and(InventoryMovement::where('source_id', $order->id)->count())->toBe(0);
});
