<?php

use App\Domains\Order\Models\UserColumnPreference;
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
        ->assertSet('visibleColumns', fn ($cols) => in_array('provider', $cols, true) && ! in_array('delivery_rider', $cols, true))
        ->assertSee(__('order_flow.tracking_tab_carrier'))
        ->assertSee(__('order_flow.tracking_tab_rider'));
});

test('switching to the rider tab equips the unified grid with the rider column', function () {
    [$user, $store, $membership] = tsfOwner();
    $provider = tsfProvider($store, 'Dz Carriers');
    $rider = tsfRider($store, 'Switch Rider');
    tsfOrder($store, $provider, 'TRK-RD-1', OrderTrackingStatus::IN_TRANSIT->value)
        ->update(['delivery_rider_id' => $rider->id]);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.tracking.index')
        ->set('trackingTab', 'rider')
        ->assertSet('trackingTab', 'rider')
        ->assertSet('visibleColumns', fn ($cols) => in_array('delivery_rider', $cols, true))
        ->assertSet('shipments', fn ($rows) => count($rows) === 1);
});

test('the rider tab only lists orders with an assigned rider', function () {
    [$user, $store, $membership] = tsfOwner();
    $provider = tsfProvider($store, 'Dz Carriers');
    $rider = tsfRider($store, 'Selective Rider');
    tsfOrder($store, $provider, 'TRK-WITH-RIDER', OrderTrackingStatus::IN_TRANSIT->value)
        ->update(['delivery_rider_id' => $rider->id]);
    $withoutRider = tsfOrder($store, $provider, 'TRK-NO-RIDER', OrderTrackingStatus::SHIPPED->value);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.tracking.index')
        ->set('trackingTab', 'rider')
        ->assertSet('shipments', fn ($rows) => count($rows) === 1
            && collect($rows)->pluck('number')->doesntContain($withoutRider->number));
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

test('amount and city header filters narrow the grid and its stats', function () {
    [$user, $store, $membership] = tsfOwner();
    $provider = tsfProvider($store, 'Amount Co');
    $o1 = tsfOrder($store, $provider, 'TRK-AMT-1', OrderTrackingStatus::IN_TRANSIT->value);
    $o1->update(['total_amount' => 1500]);
    $o2 = tsfOrder($store, $provider, 'TRK-AMT-2', OrderTrackingStatus::IN_TRANSIT->value);
    $o2->update(['total_amount' => 800]);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.tracking.index')
        ->assertSet('stats.active', 2)
        ->call('setFilter', 'amount_min', 1000)
        ->assertSet('filters.amount_min', 1000)
        ->assertSet('filteredTotal', 1)
        ->assertSet('stats.active', 1)
        ->assertSet('shipments', fn ($rows) => count($rows) === 1 && collect($rows)->pluck('number')->contains($o1->number))
        ->call('setFilter', 'amount_min', null)
        ->assertSet('stats.active', 2);
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
        ->assertSet('filters.amount_min', null)
        ->assertSet('filters.city', null)
        ->assertSet('filters.rider', null)
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

test('the rider tab shows configured riders plus filter-responsive per-rider counts', function () {
    [$user, $store, $membership] = tsfOwner();
    $provider = tsfProvider($store, 'Dz Carriers');
    $riderA = tsfRider($store, 'Karim Rider');
    $riderB = tsfRider($store, 'Sami Rider');
    $order = tsfOrder($store, $provider, 'TRK-RD-1', OrderTrackingStatus::IN_TRANSIT->value);
    $order->update(['delivery_rider_id' => $riderA->id]);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.tracking.index')
        ->set('trackingTab', 'rider')
        ->assertSet('allRiders', fn ($rows) => count($rows) === 2)
        ->assertSet('riderRiders', fn ($rows) => count($rows) === 1
            && collect($rows)->firstWhere('id', $riderA->id)['total'] === 1)
        ->assertSee('Karim Rider')
        ->assertSee('Sami Rider');
});

test('filtering the rider column narrows the unified grid to that rider', function () {
    [$user, $store, $membership] = tsfOwner();
    $provider = tsfProvider($store, 'Rapid Co');
    $riderA = tsfRider($store, 'Rider Alpha');
    $riderB = tsfRider($store, 'Rider Beta');

    $a1 = tsfOrder($store, $provider, 'TRK-A1-'.uniqid(), OrderTrackingStatus::IN_TRANSIT->value);
    $a1->update(['delivery_rider_id' => $riderA->id]);
    $a2 = tsfOrder($store, $provider, 'TRK-A2-'.uniqid(), OrderTrackingStatus::OUT_FOR_DELIVERY->value);
    $a2->update(['delivery_rider_id' => $riderA->id]);
    $b1 = tsfOrder($store, $provider, 'TRK-B1-'.uniqid(), OrderTrackingStatus::SHIPPED->value);
    $b1->update(['delivery_rider_id' => $riderB->id]);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.tracking.index')
        ->set('trackingTab', 'rider')
        ->assertSet('shipments', fn ($rows) => count($rows) === 3)
        ->call('setFilter', 'rider', $riderA->id)
        ->assertSet('filters.rider', $riderA->id)
        ->assertSet('shipments', fn ($rows) => count($rows) === 2
            && collect($rows)->pluck('number')->contains($a1->number)
            && collect($rows)->pluck('number')->contains($a2->number)
            && collect($rows)->pluck('number')->doesntContain($b1->number));
});

test('assigning a rider from the drawer updates the order and the overview', function () {
    [$user, $store, $membership] = tsfOwner();
    $provider = tsfProvider($store, 'Dz Carriers');
    $order = tsfOrder($store, $provider, 'TRK-ASSIGN-1', OrderTrackingStatus::SHIPPED->value);
    $order->update(['shipping_provider_id' => null]);
    $rider = tsfRider($store, 'Assign Rider');

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.tracking.index')
        ->set('trackingTab', 'rider')
        ->call('assignRider', (string) $order->id, $rider->id)
        ->assertDispatched('swal:toast', fn ($name, $params) => ($params[0]['icon'] ?? null) === 'success')
        ->assertSet('riderRiders', fn ($rows) => collect($rows)->firstWhere('id', $rider->id)['total'] === 1);

    expect($order->refresh()->delivery_rider_id)->toBe($rider->id);
});

test('assigning a rider creates an open tracking with a generated HM/SD number', function () {
    [$user, $store, $membership] = tsfOwner();
    $rider = tsfRider($store, 'Rider Gen');

    $order = Order::create([
        'store_id' => $store->id,
        'status_id' => tsfCarrierStatus()->id,
        'number' => (new Order(['store_id' => $store->id]))->nextOrderNumber(),
        'total_amount' => 1800,
        'shipping_provider_id' => null,
        'delivery_type' => 'home',
    ]);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.tracking.index')
        ->set('trackingTab', 'rider')
        ->call('assignRider', (string) $order->id, $rider->id);

    $tracking = OrderTracking::where('order_id', $order->id)->latest('created_at')->first();

    expect($order->refresh()->delivery_rider_id)->toBe($rider->id)
        ->and($tracking)->not->toBeNull()
        ->and($tracking->tracking_number)->toStartWith('HM-')
        ->and($tracking->tracking_status)->toBe(OrderTrackingStatus::SHIPPED->value)
        ->and($tracking->shipping_provider_id)->toBeNull()
        ->and($tracking->delivered_at)->toBeNull();

    // Re-assigning the same rider must not duplicate the tracking leg.
    Volt::test('merchant.tracking.index')
        ->set('trackingTab', 'rider')
        ->call('assignRider', (string) $order->id, $rider->id);

    expect(OrderTracking::where('order_id', $order->id)->count())->toBe(1);
});

test('the stopdesk delivery type is stamped with an SD tracking prefix', function () {
    [$user, $store, $membership] = tsfOwner();
    $rider = tsfRider($store, 'Rider Stopdesk');

    $order = Order::create([
        'store_id' => $store->id,
        'status_id' => tsfCarrierStatus()->id,
        'number' => (new Order(['store_id' => $store->id]))->nextOrderNumber(),
        'total_amount' => 1400,
        'shipping_provider_id' => null,
        'delivery_type' => 'stopdesk',
    ]);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.tracking.index')
        ->set('trackingTab', 'rider')
        ->call('assignRider', (string) $order->id, $rider->id);

    expect(OrderTracking::where('order_id', $order->id)->latest('created_at')->first()->tracking_number)->toStartWith('SD-');
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

test('the rider tab renders aggregate stats for today open shipments, scoped to the active filters', function () {
    [$user, $store, $membership] = tsfOwner();
    $provider = tsfProvider($store, 'Rapid Co');
    $otherProvider = tsfProvider($store, 'Slow Co');
    $riderA = tsfRider($store, 'Stats Rider A');
    $riderB = tsfRider($store, 'Stats Rider B');

    $o1 = tsfOrder($store, $provider, 'TRK-ST-1', OrderTrackingStatus::IN_TRANSIT->value);
    $o1->update(['delivery_rider_id' => $riderA->id, 'total_amount' => 1500]);
    $o2 = tsfOrder($store, $provider, 'TRK-ST-2', OrderTrackingStatus::OUT_FOR_DELIVERY->value);
    $o2->update(['delivery_rider_id' => $riderA->id, 'total_amount' => 800]);
    $o3 = tsfOrder($store, $otherProvider, 'TRK-ST-3', OrderTrackingStatus::SHIPPED->value);
    $o3->update(['delivery_rider_id' => $riderB->id, 'total_amount' => 3400]);

    // Delivered today → excluded from the active/COD aggregates.
    $o4 = tsfOrder($store, $otherProvider, 'TRK-ST-4', OrderTrackingStatus::DELIVERED->value);
    $o4->update(['delivery_rider_id' => $riderA->id, 'total_amount' => 9999]);
    OrderTracking::where('order_id', $o4->id)->first()->update(['delivered_at' => now()]);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    $test = Volt::test('merchant.tracking.index')
        ->set('trackingTab', 'rider')
        ->assertSet('riderStatsActiveCount', 2)
        ->assertSet('riderStatsActiveShipments', 3)
        ->assertSet('riderStatsCodDueToday', 1500 + 800 + 3400)
        ->assertSee(__('order_flow.rider_stats_active'))
        ->assertSee(__('order_flow.rider_stats_active_shipments'))
        ->assertSee(__('order_flow.rider_stats_cod_due_today'));

    // The provider filter narrows the rider aggregates too (o3 belongs to another provider).
    $test->call('setFilter', 'provider', $provider->id)
        ->assertSet('riderStatsActiveCount', 1)
        ->assertSet('riderStatsActiveShipments', 2)
        ->assertSet('riderStatsCodDueToday', 1500 + 800);
});

test('the unified grid paginates with next/previous across all shipments', function () {
    [$user, $store, $membership] = tsfOwner();
    $provider = tsfProvider($store, 'Rapid Co');
    $rider = tsfRider($store, 'Heavy Rider');

    for ($i = 0; $i < 25; $i++) {
        tsfOrder($store, $provider, 'TRK-LM-'.str_pad((string) $i, 2, '0', STR_PAD_LEFT), OrderTrackingStatus::SHIPPED->value)
            ->update(['delivery_rider_id' => $rider->id]);
    }

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    $test = Volt::test('merchant.tracking.index')
        ->assertSet('filteredTotal', 25)
        ->assertSet('page', 1)
        ->assertSet('shipments', fn ($rows) => count($rows) === 20);

    $test->call('nextPage')
        ->assertSet('page', 2)
        ->assertSet('shipments', fn ($rows) => count($rows) === 5);

    $test->call('previousPage')
        ->assertSet('page', 1)
        ->assertSet('shipments', fn ($rows) => count($rows) === 20);
});

test('column preferences persist per view_key and reorder via the settings modal', function () {
    [$user, $store, $membership] = tsfOwner();
    $provider = tsfProvider($store, 'Col Co');
    tsfOrder($store, $provider, 'TRK-COL-1', OrderTrackingStatus::SHIPPED->value);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.tracking.index')
        ->call('openTableSettings')
        ->assertSet('showTableSettings', true)
        ->assertSet('draftColumns', fn ($cols) => in_array('city', $cols, true))
        ->call('toggleDraftColumn', 'notes')
        ->assertSet('draftColumns', fn ($cols) => ! in_array('notes', $cols, true))
        ->call('moveDraftColumn', 'city', 'up')
        ->assertSet('draftColumns', fn ($cols) => ($cols[3] ?? null) === 'city')
        ->call('saveTableSettings')
        ->assertSet('showTableSettings', false)
        ->assertSet('visibleColumns', fn ($cols) => ($cols[3] ?? null) === 'city' && ! in_array('notes', $cols, true));

    $pref = UserColumnPreference::where('membership_id', $membership->id)
        ->where('view_key', 'tracking_carrier')
        ->first();

    expect($pref)->not->toBeNull()
        ->and($pref->visible_columns)->toBe([
            'number',
            'tracking_number',
            'customer',
            'city',
            'state',
            'total',
            'tracking_status',
            'provider',
            'assigned_to',
            'confirmed_by',
            'shipping_date',
            'actions',
        ]);

    // A fresh mount restores the saved order.
    Volt::test('merchant.tracking.index')
        ->assertSet('visibleColumns', fn ($cols) => ($cols[3] ?? null) === 'city' && ! in_array('notes', $cols, true));
});

test('mandatory columns can never be hidden via the settings modal', function () {
    [$user, $store, $membership] = tsfOwner();
    $provider = tsfProvider($store, 'Lock Co');
    tsfOrder($store, $provider, 'TRK-LOCK-1', OrderTrackingStatus::SHIPPED->value);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.tracking.index')
        ->call('openTableSettings')
        ->call('toggleDraftColumn', 'number')
        ->call('toggleDraftColumn', 'customer')
        ->call('toggleDraftColumn', 'tracking_status')
        ->assertSet('draftColumns', fn ($cols) => in_array('number', $cols, true)
            && in_array('customer', $cols, true)
            && in_array('tracking_status', $cols, true))
        ->call('saveTableSettings')
        ->assertSet('visibleColumns', fn ($cols) => in_array('number', $cols, true)
            && in_array('customer', $cols, true)
            && in_array('tracking_status', $cols, true));
});

test('table style (status tint) persists per tab', function () {
    [$user, $store, $membership] = tsfOwner();
    $provider = tsfProvider($store, 'Style Co');
    tsfOrder($store, $provider, 'TRK-STYLE-1', OrderTrackingStatus::SHIPPED->value);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.tracking.index')
        ->call('openTableSettings')
        ->set('draftStyle', 'status')
        ->call('saveTableSettings')
        ->assertSet('tableStyle', 'status');

    expect(UserColumnPreference::where('membership_id', $membership->id)
        ->where('view_key', 'tracking_carrier')->first()->table_style)->toBe('status');
});

test('the active tab is persisted to browser storage, not the database', function () {
    [$user, $store, $membership] = tsfOwner();
    $provider = tsfProvider($store, 'Tab Co');
    tsfOrder($store, $provider, 'TRK-TAB-1', OrderTrackingStatus::SHIPPED->value);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.tracking.index')
        ->assertSet('trackingTab', 'carrier')
        ->assertSee('edz-tracking-active-tab')
        ->set('trackingTab', 'rider')
        ->assertSet('trackingTab', 'rider');

    // No tab state is stored server-side — the schema has no active_tab column.
    expect(\Illuminate\Support\Facades\Schema::hasColumn('user_column_preferences', 'active_tab'))->toBeFalse();
});