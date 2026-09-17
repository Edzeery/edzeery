<?php

use App\Domains\Order\Services\OrderService;
use App\Domains\Status\StatusResolver;
use App\Models\Customer;
use App\Models\InventoryMovement;
use App\Models\Locations\City;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Models\Orders\OrderStatusHistory;
use App\Models\Products\Product;
use App\Models\Products\ProductVariant;
use App\Models\Status;
use App\Models\Stores\Store;
use App\Services\Stores\StoreStatusService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(Database\Seeders\SystemStatusesSeeder::class);
    StatusResolver::flush();
});

function branchStore(): Store
{
    return Store::create([
        'user_id' => \App\Models\User::factory()->create()->id,
        'name' => 'Branch Store',
        'slug' => 'brn-'.uniqid(),
        'status' => 'active',
    ]);
}

function branchVariant(Store $store, int $stock = 10, int $price = 500): ProductVariant
{
    $product = Product::create([
        'store_id' => $store->id,
        'name' => 'Branch Product',
        'slug' => 'brn-pr-'.uniqid(),
        'sku' => 'brn-sku-'.uniqid(),
        'type' => 'variable',
        'price' => $price,
        'is_active' => true,
    ]);

    return ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Default',
        'sku' => 'brn-v-'.uniqid(),
        'price' => $price,
        'stock' => $stock,
        'is_active' => true,
    ]);
}

function branchOrder(Store $store, ProductVariant $variant, int $qty = 2): Order
{
    [$state, $city] = branchGeography();

    $customer = Customer::create([
        'store_id' => $store->id,
        'name' => 'Branch Customer',
        'phone' => '0550000000',
        'status' => true,
    ]);

    $pending = Status::system()->forType('order')->where('key', 'pending')->first();

    $order = Order::create([
        'store_id' => $store->id,
        'customer_id' => $customer->id,
        'status_id' => $pending?->id,
        'number' => (new Order(['store_id' => $store->id]))->nextOrderNumber(),
        'total_amount' => $qty * $variant->price,
        'state_id' => $state->id,
        'city_id' => $city->id,
        'delivery_type' => 'home',
        'payment_method' => 'cod',
        'shipping_cost' => 0,
    ]);

    OrderItem::create([
        'store_id' => $store->id,
        'order_id' => $order->id,
        'product_variant_id' => $variant->id,
        'product_id' => $variant->product_id,
        'quantity' => $qty,
        'price' => $variant->price,
        'subtotal' => $qty * $variant->price,
    ]);

    return $order;
}

function branchGeography(): array
{
    $country = Country::create([
        'name' => 'Algeria',
        'code' => 'DZ',
        'is_active' => true,
        'is_cod_available' => true,
    ]);

    $state = State::create([
        'country_id' => $country->id,
        'state_code' => '16',
        'name' => 'Alger',
        'is_active' => true,
        'is_cod_available' => true,
    ]);

    $city = City::create([
        'state_id' => $state->id,
        'name' => 'Bab Ezzouar',
        'post_code' => '16028',
        'is_active' => true,
        'is_cod_available' => true,
    ]);

    return [$state, $city];
}

test('the resolver surfaces custom rider and confirmation statuses by store', function () {
    $store = branchStore();

    $service = app(StoreStatusService::class);
    $rider = $service->addStatus((string) $store->id, StoreStatusService::TRACKING_TYPE, 'Pickup scheduled', 'info');
    $conf = $service->addStatus((string) $store->id, StoreStatusService::TYPE, 'Contacted and confirmed', 'success', 'confirmed');

    expect(StatusResolver::resolve('tracking', $rider->key, $store->id)->label)->toBe('Pickup scheduled')
        ->and(StatusResolver::resolve('order', $conf->key, $store->id)->label)->toBe('Contacted and confirmed');
});

test('a custom confirmation status is a functional branch of its origin', function () {
    $store = branchStore();
    $variant = branchVariant($store, 10);
    $order = branchOrder($store, $variant, 2);

    $service = app(StoreStatusService::class);
    $custom = $service->addStatus((string) $store->id, StoreStatusService::TYPE, 'Contacted and confirmed', 'success', 'confirmed');

    $orderService = app(OrderService::class);

    // From 'pending' the origin 'confirmed' is allowed → the branch is allowed too.
    expect($orderService->canTransition($order, $custom->key))->toBeTrue()
        ->and($orderService->canTransition($order, 'bogus_nonexistent'))->toBeFalse();

    // Transitioning to the branch resolves the STORE row (no system row exists).
    $order = $orderService->transition($order->fresh(), $custom->key);

    expect($order->status_id)->toBe($custom->id)
        ->and($order->status?->key)->toBe($custom->key);

    // Inventory follows the origin: 'confirmed' → reserve, applied exactly once.
    expect($variant->fresh()->stock)->toBe(8);

    $movements = InventoryMovement::where('source_type', Order::class)
        ->where('source_id', $order->id)
        ->where('product_variant_id', $variant->id);

    expect($movements->count())->toBe(1)
        ->and($movements->first()->type)->toBe(\App\Enums\Store\InventoryMovementType::RESERVE)
        ->and($movements->first()->quantity)->toBe(2);

    // History records the custom status against the order.
    expect(OrderStatusHistory::where('order_id', $order->id)->latest('id')->first()->status_id)->toBe($custom->id);

    // Exiting the branch through an origin target ('preparing') adds no extra
    // inventory movement — reserve stays applied exactly once.
    $order = $orderService->transition($order->fresh(), 'preparing');

    expect($order->status_id)->toBe(Status::system()->forType('order')->where('key', 'preparing')->first()->id)
        ->and($movements->count())->toBe(1)
        ->and($variant->fresh()->stock)->toBe(8);
});

test('an order sitting on a branch status inherits its origin transitions', function () {
    $store = branchStore();
    $variant = branchVariant($store);
    $order = branchOrder($store, $variant, 1);

    $custom = app(StoreStatusService::class)
        ->addStatus((string) $store->id, StoreStatusService::TYPE, 'Contacted and confirmed', 'success', 'confirmed');

    $orderService = app(OrderService::class);
    $order = $orderService->transition($order->fresh(), $custom->key);

    // The branch is not a dead end: it inherits the origin's ('confirmed') exits.
    $transitions = $orderService->availableTransitions($order);

    expect($transitions)->toContain('preparing')
        ->and($transitions)->toContain('pending')
        ->and($transitions)->toContain('cancelled');
});

test('transition by an unknown key still throws even with the branch resolver', function () {
    $store = branchStore();
    $variant = branchVariant($store);
    $order = branchOrder($store, $variant, 1);

    expect(fn () => app(OrderService::class)->transition($order->fresh(), 'does_not_exist'))
        ->toThrow(ModelNotFoundException::class);
});