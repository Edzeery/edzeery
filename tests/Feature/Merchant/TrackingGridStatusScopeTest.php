<?php

use App\Domains\Shipping\Models\DeliveryRider;
use App\Domains\Shipping\Models\ShippingProvider;
use App\Domains\Order\Support\OrderWorkflow;
use App\Enums\Store\OrderTrackingStatus;
use App\Enums\Store\StoreRoleEnum;
use App\Models\Customer;
use App\Models\Orders\Order;
use App\Models\Orders\OrderTracking;
use App\Models\Status;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use Livewire\Volt\Volt;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\StoreRolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\SystemStatusesSeeder::class);
});

function tgsOwner(): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate(StoreRoleEnum::OWNER->value, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Tracking Grid Scope Store',
        'slug' => 'tgs-'.uniqid(),
        'status' => 'active',
    ]);

    $membership = StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'invited_by' => $user->id,
        'is_active' => true,
        'role' => StoreRoleEnum::OWNER->value,
    ]);

    return [$user, $store, $membership];
}

function tgsCarrierStatus(): Status
{
    return Status::system()
        ->forType('order')
        ->where('key', 'shipped')
        ->firstOrFail();
}

function tgsProvider(Store $store): ShippingProvider
{
    return ShippingProvider::create([
        'store_id' => $store->id,
        'name' => 'Scope Carrier',
        'code' => 'SCP',
        'is_active' => true,
        'credentials' => [],
        'shipment_types_enabled' => ['delivery'],
    ]);
}

function tgsRider(Store $store): DeliveryRider
{
    return DeliveryRider::create([
        'store_id' => $store->id,
        'name' => 'Scope Rider',
        'phone' => '0550'.fake()->unique()->numerify('######'),
        'vehicle_type' => DeliveryRider::VEHICLE_CAR,
        'is_active' => true,
    ]);
}

function tgsOrder(Store $store, array $overrides = []): Order
{
    $customer = Customer::firstOrCreate(
        ['store_id' => $store->id, 'phone' => '0561'.fake()->unique()->numerify('######')],
        ['name' => 'TGS '.fake()->unique()->firstName(), 'status' => true],
    );

    $order = Order::create(array_merge([
        'store_id' => $store->id,
        'customer_id' => $customer->id,
        'status_id' => tgsCarrierStatus()->id,
        'number' => (new Order(['store_id' => $store->id]))->nextOrderNumber(),
        'total_amount' => 810,
        'shipping_cost' => 0,
    ], $overrides));

    OrderTracking::create([
        'store_id' => $store->id,
        'order_id' => $order->id,
        'shipping_provider_id' => $overrides['shipping_provider_id'] ?? null,
        'tracking_number' => 'TRK-'.fake()->unique()->numerify('######'),
        'tracking_status' => OrderTrackingStatus::IN_TRANSIT->value,
    ]);

    return $order;
}

test('the carrier grid scope is type=order: a provider-sent order is listed', function () {
    [$user, $store] = tgsOwner();
    $provider = tgsProvider($store);
    $order = tgsOrder($store, ['shipping_provider_id' => $provider->id]);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.tracking.index')
        ->assertSet('trackingTab', 'carrier')
        ->assertSet('filteredTotal', 1)
        ->assertSet('shipments', fn ($rows) => count($rows) === 1 && collect($rows)->pluck('number')->contains($order->number));
});

test('the rider grid shares the same type=order status scope and lists a rider-assigned order', function () {
    [$user, $store] = tgsOwner();
    $provider = tgsProvider($store);
    $rider = tgsRider($store);
    $order = tgsOrder($store, [
        'shipping_provider_id' => $provider->id,
        'delivery_rider_id' => $rider->id,
    ]);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.tracking.index')
        ->set('trackingTab', 'rider')
        ->assertSet('filteredTotal', 1)
        ->assertSet('shipments', fn ($rows) => count($rows) === 1 && collect($rows)->pluck('number')->contains($order->number));
});

test('the tracking grid scope is centralized and never falls back to tracking-type ids', function () {
    $carrierOrderIds = OrderWorkflow::carrierStatusIds();

    $trackingIds = Status::system()->forType('tracking')
        ->whereIn('key', OrderWorkflow::carrier())
        ->pluck('id')
        ->all();

    expect($carrierOrderIds)->not->toBe($trackingIds)
        ->and(array_intersect($carrierOrderIds, $trackingIds))->toBeEmpty()
        ->and(Order::whereNotNull('shipping_provider_id')
            ->whereIn('status_id', $trackingIds)
            ->count())->toBe(0);
});