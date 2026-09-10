<?php

use App\Domains\Shipping\Models\Carrier;
use App\Domains\Shipping\Models\CarrierPlatform;
use App\Domains\Shipping\Models\DeliveryRider;
use App\Domains\Shipping\Models\ShippingProvider;
use App\Enums\Store\OrderTrackingStatus;
use App\Enums\Store\StoreRoleEnum;
use App\Models\Customer;
use App\Models\Orders\Order;
use App\Models\Orders\OrderTracking;
use App\Models\Orders\OrderTrackingHistory;
use App\Models\Status;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use Illuminate\Support\Facades\Http;
use Livewire\Volt\Volt;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\StoreRolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\SystemStatusesSeeder::class);
});

function tsfOwner(string $storeRole = StoreRoleEnum::OWNER->value): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate($storeRole, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Tracking Search Store',
        'slug' => 'tsf-'.uniqid(),
        'status' => 'active',
    ]);

    $membership = StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'invited_by' => $user->id,
        'is_active' => true,
        'role' => $storeRole,
    ]);

    return [$user, $store, $membership];
}

function tsfCarrierStatus(): Status
{
    return Status::system()
        ->forType('order')
        ->where('key', 'shipped')
        ->firstOrFail();
}

function tsfProvider(Store $store, string $name): ShippingProvider
{
    return ShippingProvider::create([
        'store_id' => $store->id,
        'name' => $name,
        'code' => strtoupper(substr($name, 0, 3)),
        'is_active' => true,
        'credentials' => [],
        'shipment_types_enabled' => ['delivery'],
    ]);
}

function tsfOrder(Store $store, ShippingProvider $provider, string $trackingNumber, string $trackingStatus): Order
{
    $customer = Customer::firstOrCreate(
        ['store_id' => $store->id, 'phone' => '0560'.fake()->unique()->numerify('######')],
        ['name' => 'TSF ' . fake()->unique()->firstName(), 'status' => true],
    );

    $order = Order::create([
        'store_id' => $store->id,
        'customer_id' => $customer->id,
        'status_id' => tsfCarrierStatus()->id,
        'number' => (new Order(['store_id' => $store->id]))->nextOrderNumber(),
        'total_amount' => 900,
        'shipping_cost' => 0,
        'shipping_provider_id' => $provider->id,
    ]);

    OrderTracking::create([
        'store_id' => $store->id,
        'order_id' => $order->id,
        'shipping_provider_id' => $provider->id,
        'tracking_number' => $trackingNumber,
        'tracking_status' => $trackingStatus,
    ]);

    return $order;
}

function tsfNumbers(array $rows): array
{
    return $rows;
}

function tsfRider(Store $store, string $name): DeliveryRider
{
    return DeliveryRider::create([
        'store_id' => $store->id,
        'name' => $name,
        'phone' => '0550'.fake()->unique()->numerify('######'),
        'vehicle_type' => DeliveryRider::VEHICLE_CAR,
        'is_active' => true,
    ]);
}

function tsfNoestProvider(Store $store): ShippingProvider
{
    $platform = CarrierPlatform::create(['name' => 'Noest', 'slug' => 'noest-'.uniqid(), 'is_active' => true]);

    $carrier = Carrier::create([
        'platform_id' => $platform->id,
        'name' => 'NOEST',
        'code' => 'noest',
        'credential_fields' => [],
        'is_active' => true,
    ]);

    return ShippingProvider::create([
        'store_id' => $store->id,
        'name' => 'NOEST DZ',
        'code' => 'noest',
        'carrier_id' => $carrier->id,
        'credentials' => ['api_token' => 'token-'.uniqid(), 'api_base' => 'https://noest.test/api/public'],
        'is_active' => true,
        'shipment_types_enabled' => ['delivery'],
    ]);
}

function tsfNoestEntry(ShippingProvider $provider, string $trackingNumber, array $rows): array
{
    $activity = [];

    foreach (array_values($rows) as $index => $row) {
        $activity[] = [
            'event' => $row['event'],
            'event_key' => $row['event_key'] ?? '',
            'causer' => $row['causer'] ?? 'NOEST',
            'badge-class' => 'badge-primary',
            'date' => '2024-03-01 '.str_pad((string) (10 + $index), 2, '0', STR_PAD_LEFT).':'.str_pad((string) ($index * 5), 2, '0', STR_PAD_LEFT).':00',
        ];
    }

    return [
        (string) $trackingNumber => [
            'OrderInfo' => [
                'tracking' => (string) $trackingNumber,
                'reference' => 'REF0003',
                'client' => 'Karim Benali',
                'phone' => '0770000000',
                'wilaya_id' => 16,
                'commune' => 'El Harrach',
                'montant' => '900.00',
            ],
            'recipientName' => 'Karim Benali',
            'activity' => $activity,
            'deliveryAttempts' => [],
        ],
    ];
}

test('the tracking page renders the two tabs and defaults to the carrier tab', function () {
    [$user, $store, $membership] = tsfOwner();
    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.tracking.index')
        ->assertSet('trackingTab', 'carrier')
        ->assertSee(__('order_flow.tracking_tab_carrier'))
        ->assertSee(__('order_flow.tracking_tab_rider'));
});

test('switching to the rider tab renders the placeholder for now', function () {
    [$user, $store, $membership] = tsfOwner();
    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.tracking.index')
        ->set('trackingTab', 'rider')
        ->assertSet('trackingTab', 'rider')
        ->assertSee(__('order_flow.rider_tab_empty_title'));
});

test('search narrows the list by tracking number and restores on clear', function () {
    [$user, $store, $membership] = tsfOwner();
    $provider = tsfProvider($store, 'Dz Carriers');
    $alpha = tsfOrder($store, $provider, 'TRK-SEARCH-001', OrderTrackingStatus::IN_TRANSIT->value);
    $beta = tsfOrder($store, $provider, 'TRK-SEARCH-002', OrderTrackingStatus::OUT_FOR_DELIVERY->value);
    $gamma = tsfOrder($store, $provider, 'ZZZ-CLEAR-003', OrderTrackingStatus::SHIPPED->value);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.tracking.index')
        ->set('search', 'TRK-SEARCH')
        ->assertSet('search', 'TRK-SEARCH')
        ->assertSet('filteredTotal', 2)
        ->assertSet('shipments', fn ($rows) => count($rows) === 2
            && collect($rows)->pluck('number')->contains($alpha->number)
            && collect($rows)->pluck('number')->contains($beta->number)
            && collect($rows)->pluck('number')->doesntContain($gamma->number))
        ->set('search', '')
        ->assertSet('shipments', fn ($rows) => count($rows) === 3);
});

test('multi-status filter toggles and combines tracking statuses', function () {
    [$user, $store, $membership] = tsfOwner();
    $provider = tsfProvider($store, 'Rapid');
    $in = tsfOrder($store, $provider, 'TRK-IN-'.uniqid(), OrderTrackingStatus::IN_TRANSIT->value);
    $out = tsfOrder($store, $provider, 'TRK-OUT-'.uniqid(), OrderTrackingStatus::OUT_FOR_DELIVERY->value);
    $shipped = tsfOrder($store, $provider, 'TRK-SHP-'.uniqid(), OrderTrackingStatus::SHIPPED->value);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.tracking.index')
        ->call('toggleTrackingStatus', OrderTrackingStatus::IN_TRANSIT->value)
        ->assertSet('filters.tracking_statuses', [OrderTrackingStatus::IN_TRANSIT->value])
        ->assertSet('shipments', fn ($rows) => count($rows) === 1 && collect($rows)->pluck('number')->contains($in->number))
        ->call('toggleTrackingStatus', OrderTrackingStatus::OUT_FOR_DELIVERY->value)
        ->assertSet('filters.tracking_statuses', [OrderTrackingStatus::IN_TRANSIT->value, OrderTrackingStatus::OUT_FOR_DELIVERY->value])
        ->assertSet('shipments', fn ($rows) => collect($rows)->pluck('number')->contains($in->number)
            && collect($rows)->pluck('number')->contains($out->number)
            && collect($rows)->pluck('number')->doesntContain($shipped->number))
        ->call('toggleTrackingStatus', OrderTrackingStatus::IN_TRANSIT->value)
        ->assertSet('filters.tracking_statuses', [OrderTrackingStatus::OUT_FOR_DELIVERY->value])
        ->assertSet('shipments', fn ($rows) => count($rows) === 1 && collect($rows)->pluck('number')->contains($out->number));
});

test('provider filter narrows to a single shipping provider and counts the filtered total', function () {
    [$user, $store, $membership] = tsfOwner();
    $a = tsfProvider($store, 'Aramex DZ');
    $b = tsfProvider($store, 'Yalidine DZ');
    $o1 = tsfOrder($store, $a, 'TRK-A-'.uniqid(), OrderTrackingStatus::SHIPPED->value);
    $o2 = tsfOrder($store, $b, 'TRK-B-'.uniqid(), OrderTrackingStatus::SHIPPED->value);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.tracking.index')
        ->call('setFilter', 'provider', $a->id)
        ->assertSet('filters.provider', $a->id)
        ->assertSet('filteredTotal', 1)
        ->assertSet('shipments', fn ($rows) => count($rows) === 1 && collect($rows)->pluck('number')->contains($o1->number))
        ->call('setFilter', 'provider', null)
        ->assertSet('filteredTotal', 2)
        ->assertSet('shipments', fn ($rows) => count($rows) === 2);
});

test('clearFilters resets search and filters back to the full list', function () {
    [$user, $store, $membership] = tsfOwner();
    $provider = tsfProvider($store, 'Clear Co');
    tsfOrder($store, $provider, 'TRK-CLEAR-1', OrderTrackingStatus::IN_TRANSIT->value);
    tsfOrder($store, $provider, 'TRK-CLEAR-2', OrderTrackingStatus::SHIPPED->value);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.tracking.index')
        ->call('toggleTrackingStatus', OrderTrackingStatus::IN_TRANSIT->value)
        ->set('search', 'TRK-CLEAR-1')
        ->call('clearFilters')
        ->assertSet('search', '')
        ->assertSet('filters.tracking_statuses', [])
        ->assertSet('filters.provider', null)
        ->assertSet('shipments', fn ($rows) => count($rows) === 2);
});

test('the drawer sync action re-polls the carrier and refreshes the open drawer', function () {
    [$user, $store, $membership] = tsfOwner();
    $provider = tsfNoestProvider($store);
    $order = tsfOrder($store, $provider, 'TRK-SYNC-001', OrderTrackingStatus::IN_TRANSIT->value);

    Http::fake([
        'noest.test/*' => Http::response(tsfNoestEntry($provider, 'TRK-SYNC-001', [
            ['event' => 'Delivered', 'event_key' => 'livre'],
        ])),
    ]);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    $tracking = OrderTracking::where('order_id', $order->id)->firstOrFail();

    Volt::test('merchant.tracking.index')
        ->call('openDrawer', (string) $order->id)
        ->assertSet('drawerOrderId', (string) $order->id)
        ->assertSet('drawerTracking.tracking_status', OrderTrackingStatus::IN_TRANSIT->value)
        ->call('syncTracking', (string) $tracking->id)
        ->assertSet('drawerTracking.tracking_status', OrderTrackingStatus::DELIVERED->value)
        ->assertSet('shipments', fn ($rows) => collect($rows)->pluck('tracking_status')->contains(OrderTrackingStatus::DELIVERED->value));

    expect($tracking->refresh()->tracking_status)->toBe(OrderTrackingStatus::DELIVERED->value)
        ->and($tracking->last_synced_at)->not->toBeNull()
        ->and(OrderTrackingHistory::where('order_tracking_id', $tracking->id)->where('status', OrderTrackingStatus::DELIVERED->value)->exists())->toBeTrue();
});

test('the drawer sync action does not render for a shipment without a tracking number', function () {
    [$user, $store, $membership] = tsfOwner();
    $provider = tsfProvider($store, 'Plain Co');
    $order = tsfOrder($store, $provider, 'PLAIN-001', OrderTrackingStatus::SHIPPED->value);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    $tracking = OrderTracking::where('order_id', $order->id)->firstOrFail();
    $tracking->update(['tracking_number' => null]);

    Volt::test('merchant.tracking.index')
        ->call('openDrawer', (string) $order->id)
        ->assertSet('drawerOrderId', (string) $order->id)
        ->assertDontSee(__('order_flow.tracking_sync_section'));
});

test('the rider tab lists store riders with their shipment counts', function () {
    [$user, $store, $membership] = tsfOwner();
    $provider = tsfProvider($store, 'Dz Carriers');
    $riderA = tsfRider($store, 'Karim Rider');
    $riderB = tsfRider($store, 'Sami Rider');
    tsfOrder($store, $provider, 'TRK-RD-1', OrderTrackingStatus::IN_TRANSIT->value)
        ->update(['delivery_rider_id' => $riderA->id]);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.tracking.index')
        ->set('trackingTab', 'rider')
        ->assertSet('riderRiders', fn ($rows) => count($rows) === 2
            && collect($rows)->firstWhere('id', $riderA->id)['total'] === 1
            && collect($rows)->firstWhere('id', $riderA->id)['active'] === 1
            && collect($rows)->firstWhere('id', $riderB->id)['total'] === 0)
        ->assertSee('Karim Rider')
        ->assertSee('Sami Rider');
});

test('expanding a rider shows only that rider shipments', function () {
    [$user, $store, $membership] = tsfOwner();
    $provider = tsfProvider($store, 'Rapid Co');
    $riderA = tsfRider($store, 'Rider Alpha');
    $riderB = tsfRider($store, 'Rider Beta');

    $a1 = tsfOrder($store, $provider, 'TRK-A1-'.uniqid(), OrderTrackingStatus::IN_TRANSIT->value)->update(['delivery_rider_id' => $riderA->id]);
    $a2 = tsfOrder($store, $provider, 'TRK-A2-'.uniqid(), OrderTrackingStatus::OUT_FOR_DELIVERY->value)->update(['delivery_rider_id' => $riderA->id]);
    $b1 = tsfOrder($store, $provider, 'TRK-B1-'.uniqid(), OrderTrackingStatus::SHIPPED->value)->update(['delivery_rider_id' => $riderB->id]);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.tracking.index')
        ->set('trackingTab', 'rider')
        ->call('toggleRider', $riderA->id)
        ->assertSet('selectedRiderId', $riderA->id)
        ->assertSet('riderShipmentTotal', 2)
        ->assertSet('riderShipments', fn ($rows) => count($rows) === 2)
        ->call('toggleRider', $riderA->id)
        ->assertSet('selectedRiderId', null)
        ->assertSet('riderShipments', []);
});

test('assigning a rider from the drawer updates the order and the overview', function () {
    [$user, $store, $membership] = tsfOwner();
    $provider = tsfProvider($store, 'Dz Carriers');
    $order = tsfOrder($store, $provider, 'TRK-ASSIGN-1', OrderTrackingStatus::SHIPPED->value);
    $order->update(['shipping_provider_id' => null]);
    $rider = tsfRider($store, 'Assign Rider');

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.tracking.index')
        ->call('assignRider', (string) $order->id, $rider->id)
        ->assertDispatched('swal:toast', fn ($name, $params) => ($params[0]['icon'] ?? null) === 'success')
        ->assertSet('riderRiders', fn ($rows) => collect($rows)->firstWhere('id', $rider->id)['total'] === 1);

    expect($order->refresh()->delivery_rider_id)->toBe($rider->id);
});

test('assigning a rider is refused when the order already has a shipping provider', function () {
    [$user, $store, $membership] = tsfOwner();
    $provider = tsfProvider($store, 'Aramex DZ');
    $order = tsfOrder($store, $provider, 'TRK-PROV-1', OrderTrackingStatus::SHIPPED->value);
    $rider = tsfRider($store, 'Blocked Rider');

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.tracking.index')
        ->call('assignRider', (string) $order->id, $rider->id)
        ->assertDispatched('swal:toast', fn ($name, $params) => ($params[0]['icon'] ?? null) === 'warning');

    expect($order->refresh()->delivery_rider_id)->toBeNull();
});

test('assigning a rider requires the order.assign permission', function () {
    [$staffUser, $store, $membership] = tsfOwner(StoreRoleEnum::STAFF->value);
    $provider = tsfProvider($store, 'Rapid Co');
    $order = tsfOrder($store, $provider, 'TRK-STAFF-1', OrderTrackingStatus::SHIPPED->value);
    $rider = tsfRider($store, 'Staff Rider');

    actingAs($staffUser)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.tracking.index')
        ->call('assignRider', (string) $order->id, $rider->id)
        ->assertForbidden();

    expect($order->refresh()->delivery_rider_id)->toBeNull();
});