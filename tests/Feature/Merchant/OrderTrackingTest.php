<?php

use App\Domains\Order\Services\OrderService;
use App\Domains\Order\Services\OrderTrackingService;
use App\Domains\Shipping\Models\ShippingProvider;
use App\Models\Locations\City;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use App\Models\Orders\Order;
use App\Models\Orders\OrderTracking;
use App\Models\Products\Product;
use App\Models\Products\ProductVariant;
use App\Models\Stores\Store;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

function otStore(): Store
{
    $user = \App\Models\User::factory()->create();

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Tracking Store',
        'slug' => 'trk-'.uniqid(),
        'status' => 'active',
        'landing_template' => 'catalog',
    ]);

    config(['app.domain' => 'example.test']);
    test()->withSession(['current_store_id' => $store->id]);

    return $store;
}

function otOrder(Store $store, ProductVariant $variant, string $statusKey = 'pending'): Order
{
    test()->artisan('db:seed', ['--class' => \Database\Seeders\SystemStatusesSeeder::class, '--force' => true]);

    $country = Country::create(['name' => 'Trk Land', 'code' => 'TL', 'is_active' => true]);
    $state = State::create([
        'country_id' => $country->id,
        'state_code' => 'TL-01',
        'name' => 'Trk State',
        'is_active' => true,
        'is_cod_available' => true,
    ]);
    $city = City::create([
        'state_id' => $state->id,
        'name' => 'Trk City',
        'post_code' => '0000',
        'is_active' => true,
    ]);

    $customer = \App\Models\Customer::create([
        'store_id' => $store->id,
        'name' => 'Track Customer',
        'phone' => '0550000000',
        'status' => true,
    ]);

    $status = \App\Models\Status::system()->forType('order')->where('key', $statusKey)->first();

    $order = Order::create([
        'store_id' => $store->id,
        'customer_id' => $customer->id,
        'status_id' => $status?->id,
        'number' => (new Order(['store_id' => $store->id]))->nextOrderNumber(),
        'total_amount' => 1000,
        'state_id' => $state->id,
        'city_id' => $city->id,
        'delivery_type' => 'home',
        'payment_method' => 'cod',
    ]);

    \App\Models\Orders\OrderItem::create([
        'store_id' => $store->id,
        'order_id' => $order->id,
        'product_variant_id' => $variant->id,
        'product_id' => $variant->product_id,
        'quantity' => 1,
        'price' => $variant->price,
        'subtotal' => $variant->price,
    ]);

    return $order;
}

test('shipped transition creates one tracking record with correct shipping_provider_id', function () {
    $store = otStore();
    $product = Product::create([
        'store_id' => $store->id,
        'name' => 'Trk Product',
        'slug' => 'trk-pr-'.uniqid(),
        'sku' => 'trk-sku-'.uniqid(),
        'type' => 'variable',
        'price' => 500,
        'is_active' => true,
    ]);
    $variant = ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Default',
        'sku' => 'trk-v-'.uniqid(),
        'price' => 500,
        'stock' => 10,
    ]);

    $provider = ShippingProvider::create([
        'store_id' => $store->id,
        'name' => 'Yalidine',
        'credentials' => [],
        'is_active' => true,
    ]);

    $order = otOrder($store, $variant, 'preparing');
    $order->update(['shipping_provider_id' => $provider->id]);

    app(OrderService::class)->transition($order->fresh(), 'shipped', 'Handed to carrier');

    $tracking = OrderTracking::where('order_id', $order->id)->first();

    expect($tracking)->not->toBeNull()
        ->and($tracking->shipping_provider_id)->toBe($provider->id)
        ->and($tracking->shipped_at)->not->toBeNull()
        ->and($tracking->delivered_at)->toBeNull()
        ->and($tracking->returned_at)->toBeNull()
        ->and($tracking->webhook_token)->not->toBeNull();

    expect(OrderTracking::where('order_id', $order->id)->count())->toBe(1);
});

test('delivered after shipped updates same record without creating a new row', function () {
    $store = otStore();
    $product = Product::create([
        'store_id' => $store->id,
        'name' => 'Del Product',
        'slug' => 'del-pr-'.uniqid(),
        'sku' => 'del-sku-'.uniqid(),
        'type' => 'variable',
        'price' => 500,
        'is_active' => true,
    ]);
    $variant = ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Default',
        'sku' => 'del-v-'.uniqid(),
        'price' => 500,
        'stock' => 10,
    ]);

    $order = otOrder($store, $variant, 'preparing');
    app(OrderService::class)->transition($order->fresh(), 'shipped');

    $shippedTracking = OrderTracking::where('order_id', $order->id)->first();
    expect($shippedTracking)->not->toBeNull();

    app(OrderService::class)->transition($order->fresh(), 'delivered');

    expect(OrderTracking::where('order_id', $order->id)->count())->toBe(1);

    $tracking = $shippedTracking->fresh();
    expect($tracking->delivered_at)->not->toBeNull()
        ->and($tracking->shipped_at)->not->toBeNull();
});

test('returned after shipped sets returned_at on the same record', function () {
    $store = otStore();
    $product = Product::create([
        'store_id' => $store->id,
        'name' => 'Ret Product',
        'slug' => 'ret-pr-'.uniqid(),
        'sku' => 'ret-sku-'.uniqid(),
        'type' => 'variable',
        'price' => 500,
        'is_active' => true,
    ]);
    $variant = ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Default',
        'sku' => 'ret-v-'.uniqid(),
        'price' => 500,
        'stock' => 10,
    ]);

    $order = otOrder($store, $variant, 'preparing');
    app(OrderService::class)->transition($order->fresh(), 'shipped');
    app(OrderService::class)->transition($order->fresh(), 'returned');

    $tracking = OrderTracking::where('order_id', $order->id)->first();
    expect($tracking)->not->toBeNull()
        ->and($tracking->returned_at)->not->toBeNull()
        ->and($tracking->shipped_at)->not->toBeNull();
});

test('re-ship after return creates a distinct second tracking record', function () {
    $store = otStore();
    $product = Product::create([
        'store_id' => $store->id,
        'name' => 'Reship Product',
        'slug' => 'rs-pr-'.uniqid(),
        'sku' => 'rs-sku-'.uniqid(),
        'type' => 'variable',
        'price' => 500,
        'is_active' => true,
    ]);
    $variant = ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Default',
        'sku' => 'rs-v-'.uniqid(),
        'price' => 500,
        'stock' => 10,
    ]);

    $order = otOrder($store, $variant, 'preparing');

    // First shipment
    app(OrderService::class)->transition($order->fresh(), 'shipped');
    $first = OrderTracking::where('order_id', $order->id)->latest('created_at')->first();
    expect($first)->not->toBeNull();

    // Return
    app(OrderService::class)->transition($order->fresh(), 'returned');
    expect($first->fresh()->returned_at)->not->toBeNull();

    // Re-ship: returned → cancelled → pending → confirmed → preparing → shipped
    app(OrderService::class)->transition($order->fresh(), 'cancelled');
    app(OrderService::class)->transition($order->fresh(), 'pending');
    app(OrderService::class)->transition($order->fresh(), 'confirmed');
    app(OrderService::class)->transition($order->fresh(), 'preparing');
    app(OrderService::class)->transition($order->fresh(), 'shipped');

    $count = OrderTracking::where('order_id', $order->id)->count();
    expect($count)->toBe(2);

    // The second tracking (re-ship) should have shipped_at set and no delivered/returned
    $reShip = OrderTracking::where('order_id', $order->id)
        ->whereNull('delivered_at')
        ->whereNull('returned_at')
        ->first();
    expect($reShip)->not->toBeNull()
        ->and($reShip->id)->not->toBe($first->id)
        ->and($reShip->shipped_at)->not->toBeNull();
});

test('markDelivered and markReturned return null gracefully when no tracking exists', function () {
    $store = otStore();
    $product = Product::create([
        'store_id' => $store->id,
        'name' => 'Null Product',
        'slug' => 'nl-pr-'.uniqid(),
        'sku' => 'nl-sku-'.uniqid(),
        'type' => 'variable',
        'price' => 500,
        'is_active' => true,
    ]);
    $variant = ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Default',
        'sku' => 'nl-v-'.uniqid(),
        'price' => 500,
        'stock' => 10,
    ]);

    $order = otOrder($store, $variant, 'pending');

    $service = app(OrderTrackingService::class);
    expect($service->markDelivered($order))->toBeNull()
        ->and($service->markReturned($order))->toBeNull();
});

test('Order::trackings returns all historical records in creation order', function () {
    $store = otStore();
    $product = Product::create([
        'store_id' => $store->id,
        'name' => 'Hist Product',
        'slug' => 'hi-pr-'.uniqid(),
        'sku' => 'hi-sku-'.uniqid(),
        'type' => 'variable',
        'price' => 500,
        'is_active' => true,
    ]);
    $variant = ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Default',
        'sku' => 'hi-v-'.uniqid(),
        'price' => 500,
        'stock' => 10,
    ]);

    $order = otOrder($store, $variant, 'preparing');

    // Ship → deliver (first tracking)
    app(OrderService::class)->transition($order->fresh(), 'shipped');
    app(OrderService::class)->transition($order->fresh(), 'delivered');

    // Re-ship: delivered → returned → cancelled → pending → confirmed → preparing → shipped
    app(OrderService::class)->transition($order->fresh(), 'returned');
    app(OrderService::class)->transition($order->fresh(), 'cancelled');
    app(OrderService::class)->transition($order->fresh(), 'pending');
    app(OrderService::class)->transition($order->fresh(), 'confirmed');
    app(OrderService::class)->transition($order->fresh(), 'preparing');
    app(OrderService::class)->transition($order->fresh(), 'shipped');

    $trackings = $order->fresh()->trackings()->get();
    expect($trackings)->toHaveCount(2)
        ->and($trackings->first()->delivered_at)->not->toBeNull()
        ->and($trackings->last()->delivered_at)->toBeNull()
        ->and($trackings->last()->shipped_at)->not->toBeNull();

    // latestTracking returns the newest one
    $latest = $order->fresh()->latestTracking;
    expect($latest)->not->toBeNull()
        ->and($latest->delivered_at)->toBeNull();
});

test('tracking_status is normalised through the shipment lifecycle', function () {
    $store = otStore();
    $product = Product::create([
        'store_id' => $store->id,
        'name' => 'Norm Product',
        'slug' => 'nm-pr-'.uniqid(),
        'sku' => 'nm-sku-'.uniqid(),
        'type' => 'variable',
        'price' => 500,
        'is_active' => true,
    ]);
    $variant = ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Default',
        'sku' => 'nm-v-'.uniqid(),
        'price' => 500,
        'stock' => 10,
    ]);

    $order = otOrder($store, $variant, 'preparing');

    app(OrderService::class)->transition($order->fresh(), 'shipped');
    $tracking = OrderTracking::where('order_id', $order->id)->first();

    expect($tracking->tracking_status)->toBe(\App\Enums\Store\OrderTrackingStatus::SHIPPED->value);

    app(OrderService::class)->transition($order->fresh(), 'delivered');

    expect($tracking->fresh()->tracking_status)->toBe(\App\Enums\Store\OrderTrackingStatus::DELIVERED->value);
});

test('terminal helpers mark the open tracking record', function () {
    $store = otStore();
    $product = Product::create([
        'store_id' => $store->id,
        'name' => 'Term Product',
        'slug' => 'tm-pr-'.uniqid(),
        'sku' => 'tm-sku-'.uniqid(),
        'type' => 'variable',
        'price' => 500,
        'is_active' => true,
    ]);
    $variant = ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Default',
        'sku' => 'tm-v-'.uniqid(),
        'price' => 500,
        'stock' => 10,
    ]);

    $order = otOrder($store, $variant, 'preparing');
    $service = app(OrderTrackingService::class);

    $service->startShipment($order);
    expect($service->markFailedAttempt($order)->tracking_status)->toBe(\App\Enums\Store\OrderTrackingStatus::FAILED_ATTEMPT->value);

    $service->markReturning($order);
    expect($order->fresh()->trackings()->first()->tracking_status)->toBe(\App\Enums\Store\OrderTrackingStatus::RETURNING->value);

    // The returning record is closed (returned_at set): a re-ship opens a new record.
    $service->startShipment($order);
    expect($service->markLost($order)->tracking_status)->toBe(\App\Enums\Store\OrderTrackingStatus::LOST->value);

    expect($service->markDamaged($order)->tracking_status)->toBe(\App\Enums\Store\OrderTrackingStatus::DAMAGED->value);
});
