<?php

use App\Domains\Order\Services\OrderService;
use App\Domains\Order\Services\OrderTrackingService;
use App\Domains\Order\Services\ReturnVerificationService;
use App\Enums\Store\ReturnInspectionResult;
use App\Models\Locations\City;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use App\Models\Orders\Order;
use App\Models\Orders\OrderTracking;
use App\Models\Products\Product;
use App\Models\Products\ProductVariant;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

function rvStore(): Store
{
    $user = \App\Models\User::factory()->create();

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Return Store',
        'slug' => 'ret-' . uniqid(),
        'status' => 'active',
        'landing_template' => 'catalog',
    ]);

    config(['app.domain' => 'example.test']);
    test()->withSession(['current_store_id' => $store->id]);

    return $store;
}

function rvMembership(Store $store): StoreMembership
{
    $user = \App\Models\User::factory()->create();

    return StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'is_active' => true,
        'accepted_at' => now(),
    ]);
}

function rvOrder(Store $store, ProductVariant $variant, string $statusKey = 'returned'): Order
{
    test()->artisan('db:seed', ['--class' => \Database\Seeders\SystemStatusesSeeder::class, '--force' => true]);

    $country = Country::create(['name' => 'Ret Land', 'code' => 'RL', 'is_active' => true]);
    $state = State::create([
        'country_id' => $country->id,
        'state_code' => 'RL-01',
        'name' => 'Ret State',
        'is_active' => true,
        'is_cod_available' => true,
    ]);
    $city = City::create([
        'state_id' => $state->id,
        'name' => 'Ret City',
        'post_code' => '1100',
        'is_active' => true,
    ]);

    $customer = \App\Models\Customer::create([
        'store_id' => $store->id,
        'name' => 'Ret Customer',
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

function rvReturnedOrderWithTracking(Store $store, ProductVariant $variant): array
{
    $order = rvOrder($store, $variant, 'preparing');
    app(OrderService::class)->transition($order->fresh(), 'shipped');
    $tracking = OrderTracking::where('order_id', $order->id)->first();
    $tracking->update(['tracking_number' => 'TEST-TRK-' . uniqid()]);
    app(OrderService::class)->transition($order->fresh()->load('status'), 'returned');

    $tracking->refresh();

    return ['order' => $order->fresh(), 'tracking' => $tracking];
}

test('full happy path: scan → process(good) → requeue → order back to pending', function () {
    $store = rvStore();
    $member = rvMembership($store);
    $product = Product::create([
        'store_id' => $store->id,
        'name' => 'Ret Product',
        'slug' => 'ret-pr-' . uniqid(),
        'sku' => 'ret-sku-' . uniqid(),
        'type' => 'variable',
        'price' => 500,
        'is_active' => true,
    ]);
    $variant = ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Default',
        'sku' => 'ret-v-' . uniqid(),
        'price' => 500,
        'stock' => 10,
    ]);

    ['tracking' => $tracking] = rvReturnedOrderWithTracking($store, $variant);

    $service = app(ReturnVerificationService::class);

    // Step 1: verify
    $verified = $service->verifyByCode($store->id, $tracking->tracking_number, $member);
    expect($verified)->not->toBeNull()
        ->and($verified->verified_at)->not->toBeNull()
        ->and($verified->verified_by_membership_id)->toBe($member->id);

    // Step 2: process(good)
    $processed = $service->process($verified->fresh(), ReturnInspectionResult::GOOD, 'Looks fine', $member);
    expect($processed->inspection_result)->toBe('good')
        ->and($processed->processed_at)->not->toBeNull();

    // Step 3: requeue
    $requeued = $service->requeue($processed->fresh(), $member);
    expect($requeued->status->key)->toBe('pending')
        ->and($requeued->confirmation_attempts)->toBe(0)
        ->and($requeued->assigned_to_membership_id)->toBeNull();

    // Verify requeued_at is set
    $tracking->refresh();
    expect($tracking->requeued_at)->not->toBeNull()
        ->and($tracking->requeued_by_membership_id)->toBe($member->id);
});

test('process() rejected before verifyByCode()', function () {
    $store = rvStore();
    $member = rvMembership($store);
    $product = Product::create([
        'store_id' => $store->id,
        'name' => 'Ret Product',
        'slug' => 'ret-pr-' . uniqid(),
        'sku' => 'ret-sku-' . uniqid(),
        'type' => 'variable',
        'price' => 500,
        'is_active' => true,
    ]);
    $variant = ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Default',
        'sku' => 'ret-v-' . uniqid(),
        'price' => 500,
        'stock' => 10,
    ]);

    ['tracking' => $tracking] = rvReturnedOrderWithTracking($store, $variant);

    $service = app(ReturnVerificationService::class);

    $service->process($tracking, ReturnInspectionResult::GOOD, null, $member);
})->throws(\DomainException::class, 'Cannot process a tracking record that has not been barcode-verified.');

test('requeue() rejected for damaged/lost/partial', function () {
    $store = rvStore();
    $member = rvMembership($store);
    $product = Product::create([
        'store_id' => $store->id,
        'name' => 'Ret Product',
        'slug' => 'ret-pr-' . uniqid(),
        'sku' => 'ret-sku-' . uniqid(),
        'type' => 'variable',
        'price' => 500,
        'is_active' => true,
    ]);
    $variant = ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Default',
        'sku' => 'ret-v-' . uniqid(),
        'price' => 500,
        'stock' => 10,
    ]);

    ['tracking' => $tracking] = rvReturnedOrderWithTracking($store, $variant);

    $service = app(ReturnVerificationService::class);
    $verified = $service->verifyByCode($store->id, $tracking->tracking_number, $member);

    foreach ([ReturnInspectionResult::DAMAGED, ReturnInspectionResult::LOST, ReturnInspectionResult::PARTIAL] as $result) {
        $processed = $service->process($verified->fresh(), $result, null, $member);
        $service->requeue($processed->fresh(), $member);
    }
})->throws(\DomainException::class, 'Only orders inspected as "good" can be requeued.');

test('requeue() rejected on double-call', function () {
    $store = rvStore();
    $member = rvMembership($store);
    $product = Product::create([
        'store_id' => $store->id,
        'name' => 'Ret Product',
        'slug' => 'ret-pr-' . uniqid(),
        'sku' => 'ret-sku-' . uniqid(),
        'type' => 'variable',
        'price' => 500,
        'is_active' => true,
    ]);
    $variant = ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Default',
        'sku' => 'ret-v-' . uniqid(),
        'price' => 500,
        'stock' => 10,
    ]);

    ['tracking' => $tracking] = rvReturnedOrderWithTracking($store, $variant);

    $service = app(ReturnVerificationService::class);
    $verified = $service->verifyByCode($store->id, $tracking->tracking_number, $member);
    $processed = $service->process($verified->fresh(), ReturnInspectionResult::GOOD, null, $member);
    $service->requeue($processed->fresh(), $member);

    // Second requeue
    $service->requeue($processed->fresh(), $member);
})->throws(\DomainException::class, 'This return has already been requeued.');

test('verifyByCode returns null for unknown code', function () {
    $store = rvStore();
    $member = rvMembership($store);

    $service = app(ReturnVerificationService::class);
    $result = $service->verifyByCode($store->id, 'NONEXISTENT-CODE', $member);

    expect($result)->toBeNull();
});

test('verifyByCode returns null for already-verified tracking', function () {
    $store = rvStore();
    $member = rvMembership($store);
    $product = Product::create([
        'store_id' => $store->id,
        'name' => 'Ret Product',
        'slug' => 'ret-pr-' . uniqid(),
        'sku' => 'ret-sku-' . uniqid(),
        'type' => 'variable',
        'price' => 500,
        'is_active' => true,
    ]);
    $variant = ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Default',
        'sku' => 'ret-v-' . uniqid(),
        'price' => 500,
        'stock' => 10,
    ]);

    ['tracking' => $tracking] = rvReturnedOrderWithTracking($store, $variant);

    $service = app(ReturnVerificationService::class);
    $first = $service->verifyByCode($store->id, $tracking->tracking_number, $member);
    expect($first)->not->toBeNull();

    // Scan same code again
    $second = $service->verifyByCode($store->id, $tracking->tracking_number, $member);
    expect($second)->toBeNull();
});
