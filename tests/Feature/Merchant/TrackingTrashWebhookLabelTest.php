<?php

use App\Domains\Shipping\Adapters\NoestIntegrationAdapter;
use App\Domains\Shipping\Models\Carrier;
use App\Domains\Shipping\Models\CarrierPlatform;
use App\Domains\Shipping\Models\ShippingProvider;
use App\Enums\Store\OrderTrackingStatus;
use App\Enums\Store\StorePermissionEnum;
use App\Enums\Store\StoreRoleEnum;
use App\Models\Customer;
use App\Models\Orders\Order;
use App\Models\Orders\OrderEvent;
use App\Models\Orders\OrderItem;
use App\Models\Orders\OrderStatusHistory;
use App\Models\Orders\OrderTracking;
use App\Models\Orders\OrderTrackingHistory;
use App\Models\Products\Product;
use App\Models\Products\ProductVariant;
use App\Models\Status;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Livewire\Volt\Volt;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\StoreRolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\SystemStatusesSeeder::class);
});

function twlOwner(): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate(StoreRoleEnum::OWNER->value, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Tracking Trash Store',
        'slug' => 'tracking-trash-'.uniqid(),
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

function twlStatus(string $key): Status
{
    return Status::system()->forType('order')->where('key', $key)->firstOrFail();
}

function twlNoestProvider(Store $store, string $name = 'NOEST TW'): ShippingProvider
{
    $platform = CarrierPlatform::create(['name' => 'Noest TW', 'slug' => 'noest-tw-'.uniqid(), 'is_active' => true]);

    $carrier = Carrier::create([
        'platform_id' => $platform->id,
        'name' => 'NOEST TW',
        'code' => 'noest',
        'credential_fields' => [],
        'is_active' => true,
    ]);

    return ShippingProvider::create([
        'store_id' => $store->id,
        'name' => $name,
        'code' => 'noest',
        'carrier_id' => $carrier->id,
        'credentials' => ['api_token' => 'token-'.uniqid(), 'guid' => 'guid-'.uniqid(), 'api_base' => 'https://noest.test/api/public'],
        'is_active' => true,
        'shipment_types_enabled' => ['delivery'],
    ]);
}

function twlOrder(Store $store, ShippingProvider $provider, string $trackingNumber, string $statusKey): Order
{
    $customer = Customer::create([
        'store_id' => $store->id,
        'name' => 'TWL '.fake()->unique()->firstName(),
        'phone' => '0560'.fake()->unique()->numerify('######'),
        'status' => true,
    ]);

    $order = Order::create([
        'store_id' => $store->id,
        'customer_id' => $customer->id,
        'status_id' => twlStatus($statusKey)->id,
        'number' => (new Order(['store_id' => $store->id]))->nextOrderNumber(),
        'total_amount' => 900,
        'shipping_cost' => 0,
        'shipping_provider_id' => $provider->id,
        'delivery_type' => 'home',
    ]);

    OrderTracking::create([
        'store_id' => $store->id,
        'order_id' => $order->id,
        'shipping_provider_id' => $provider->id,
        'tracking_number' => $trackingNumber,
        'tracking_status' => OrderTrackingStatus::IN_TRANSIT->value,
    ]);

    return $order;
}

function twlVolt(array $ctx): object
{
    [$user, $store] = $ctx;
    actingAs($user)->withSession(['current_store_id' => $store->id]);

    return Volt::test('merchant.tracking.index');
}

/* ───────────────────────── Column order ───────────────────────── */

test('the grid defaults to the user column order with state required and provider after status', function () {
    [$user, $store] = twlOwner();
    $provider = twlNoestProvider($store);
    twlOrder($store, $provider, 'TRK-COL-1', 'shipped');

    $volt = twlVolt([$user, $store]);

    $volt->assertSet('visibleColumns', [
        'number',
        'tracking_number',
        'customer',
        'state',
        'city',
        'total',
        'tracking_status',
        'provider',
        'assigned_to',
        'confirmed_by',
        'notes',
        'shipping_date',
        'actions',
    ]);
});

/* ───────────────────────── Trash bin ───────────────────────── */

test('trash mode lists only soft-deleted orders and restore brings them back', function () {
    [$user, $store] = twlOwner();
    $provider = twlNoestProvider($store);
    $order = twlOrder($store, $provider, 'TRK-TR-1', 'shipped');

    $volt = twlVolt([$user, $store]);
    $volt->assertSet('trashCount', 0);

    $volt->call('deleteOrder', (string) $order->id)
        ->assertDispatched('swal:toast', fn ($name, $params) => ($params[0]['icon'] ?? null) === 'success')
        ->assertSet('trashCount', 1)
        ->call('toggleTrash')
        ->assertSet('showTrash', true)
        ->assertSet('shipments', fn ($rows) => count($rows) === 1
            && collect($rows)->pluck('id')->contains((string) $order->id)
            && collect($rows)->first()['is_trashed'] === true)
        ->call('restoreOrder', (string) $order->id)
        ->assertDispatched('swal:toast', fn ($name, $params) => ($params[0]['icon'] ?? null) === 'success')
        ->assertSet('trashCount', 0)
        ->call('toggleTrash')
        ->assertSet('showTrash', false)
        ->assertSet('shipments', fn ($rows) => count($rows) === 1);

    expect(Order::find($order->id))->not->toBeNull();
});

test('restoreAll restores every trashed order at once', function () {
    [$user, $store] = twlOwner();
    $provider = twlNoestProvider($store);
    $a = twlOrder($store, $provider, 'TRK-RA-1', 'shipped');
    $b = twlOrder($store, $provider, 'TRK-RA-2', 'shipped');

    $volt = twlVolt([$user, $store]);
    $volt->call('deleteOrder', (string) $a->id)
        ->call('deleteOrder', (string) $b->id)
        ->assertSet('trashCount', 2)
        ->call('restoreAll')
        ->assertSet('trashCount', 0);

    expect(Order::find($a->id))->not->toBeNull()
        ->and(Order::find($b->id))->not->toBeNull();
});

test('forceDeleteOrder purges every child row permanently', function () {
    [$user, $store] = twlOwner();
    $provider = twlNoestProvider($store);
    $order = twlOrder($store, $provider, 'TRK-PD-1', 'shipped');

    // The carrier delete is attempted first; a carrier failure (HTTP body
    // success:false) must never block the local permanent purge.
    Http::fake(['noest.test/*' => Http::response(['success' => false, 'message' => 'test offline'])]);

    $product = Product::create([
        'store_id' => $store->id,
        'name' => 'Trash Product',
        'slug' => 'trash-pr-'.uniqid(),
        'sku' => 'trash-sku-'.uniqid(),
        'type' => 'variable',
        'price' => 900,
        'is_active' => true,
    ]);

    $variant = ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Default',
        'sku' => 'trash-v-'.uniqid(),
        'price' => 900,
        'stock' => 10,
    ]);

    OrderStatusHistory::create([
        'order_id' => $order->id,
        'status_id' => twlStatus('shipped')->id,
    ]);

    OrderItem::create([
        'store_id' => $store->id,
        'order_id' => $order->id,
        'product_id' => $variant->product_id,
        'product_variant_id' => $variant->id,
        'quantity' => 1,
        'price' => 900,
        'subtotal' => 900,
    ]);

    $tracking = OrderTracking::where('order_id', $order->id)->firstOrFail();

    OrderTrackingHistory::create([
        'store_id' => $store->id,
        'order_id' => $order->id,
        'order_tracking_id' => $tracking->id,
        'status' => 'in_transit',
    ]);

    OrderEvent::create([
        'store_id' => $store->id,
        'order_id' => $order->id,
        'event_type' => 'created',
        'payload' => [],
        'occurred_at' => now(),
    ]);

    $volt = twlVolt([$user, $store]);
    $volt->call('deleteOrder', (string) $order->id)
        ->call('toggleTrash')
        ->call('forceDeleteOrder', (string) $order->id)
        ->assertSet('trashCount', 0)
        ->assertDispatched('swal:toast', fn ($name, $params) => ($params[0]['icon'] ?? null) === 'success');

    expect(Order::withTrashed()->find($order->id))->toBeNull()
        ->and(OrderTracking::where('order_id', $order->id)->exists())->toBeFalse()
        ->and(OrderItem::where('order_id', $order->id)->exists())->toBeFalse()
        ->and(OrderStatusHistory::where('order_id', $order->id)->exists())->toBeFalse()
        ->and(OrderTrackingHistory::where('order_id', $order->id)->exists())->toBeFalse()
        ->and(\App\Models\Orders\OrderEvent::where('order_id', $order->id)->exists())->toBeFalse();
});

test('forceDeleteOrder deletes the unvalidated shipment at the carrier first', function () {
    [$user, $store] = twlOwner();
    $provider = twlNoestProvider($store);
    $order = twlOrder($store, $provider, 'TRK-CDEL-1', 'shipped');

    Http::fake(['noest.test/*' => Http::response(['success' => true, 'message' => 'deleted'])]);

    $volt = twlVolt([$user, $store]);
    $volt->call('deleteOrder', (string) $order->id)
        ->call('toggleTrash')
        ->call('forceDeleteOrder', (string) $order->id);

    Http::assertSent(fn ($request) => str_ends_with($request->url(), '/delete/order')
        && $request['tracking'] === 'TRK-CDEL-1');

    expect(Order::withTrashed()->find($order->id))->toBeNull();
});

test('forceDeleteOrder skips the carrier delete for an already-validated shipment', function () {
    [$user, $store] = twlOwner();
    $provider = twlNoestProvider($store);
    $order = twlOrder($store, $provider, 'TRK-CVAL-1', 'shipped');

    OrderTracking::where('order_id', $order->id)->update(['carrier_validated_at' => now()]);

    Http::fake(['noest.test/*' => Http::response(['success' => true, 'message' => 'deleted'])]);

    $volt = twlVolt([$user, $store]);
    $volt->call('deleteOrder', (string) $order->id)
        ->call('toggleTrash')
        ->call('forceDeleteOrder', (string) $order->id);

    Http::assertNothingSent();

    expect(Order::withTrashed()->find($order->id))->toBeNull();
});

test('force delete and soft delete are refused for members without ORDER_DELETE', function () {
    [$owner, $store] = twlOwner();
    $provider = twlNoestProvider($store);
    $order = twlOrder($store, $provider, 'TRK-GT-1', 'shipped');

    $staff = roleUser('merchant');
    $staff->assignRole(Role::findOrCreate('staff', 'merchant'));

    $membership = StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $staff->id,
        'invited_by' => $owner->id,
        'is_active' => true,
        'role' => StoreRoleEnum::STAFF->value,
    ]);
    $membership->syncPermissions([
        StorePermissionEnum::ORDER_VIEW->value,
        StorePermissionEnum::ORDER_CONFIRM->value,
    ]);

    $volt = twlVolt([$staff, $store]);

    expect(canStore(StorePermissionEnum::ORDER_DELETE->value))->toBeFalse()
        ->and($volt->get('trashCount'))->toBe(0);

    $volt->call('deleteOrder', (string) $order->id);

    expect(Order::find($order->id))->not->toBeNull()
        ->and(OrderTracking::where('order_id', $order->id)->exists())->toBeTrue();
});

/* ───────────────────────── Bulk sync ───────────────────────── */

test('syncAllTracking polls every open tracking and reports a summary', function () {
    [$user, $store] = twlOwner();
    $provider = twlNoestProvider($store);
    $order = twlOrder($store, $provider, 'TRK-SY-1', 'shipped');

    Http::fake([
        'noest.test/*' => Http::response([
            'TRK-SY-1' => [
                'OrderInfo' => ['id' => '1'],
                'activity' => [
                    ['date' => now()->subDay()->toDateTimeString(), 'event' => 'En cours', 'event_key' => 'validation_reception'],
                    ['date' => now()->toDateTimeString(), 'event' => 'Livré', 'event_key' => 'livre'],
                ],
            ],
        ]),
    ]);

    $volt = twlVolt([$user, $store]);

    $volt->call('syncAllTracking')
        ->assertDispatched('swal:toast', fn ($name, $params) => ($params[0]['icon'] ?? null) === 'success');

    expect(OrderTracking::where('order_id', $order->id)->firstOrFail()->tracking_status)
        ->toBe(OrderTrackingStatus::DELIVERED->value)
        ->and(OrderTrackingHistory::where('store_id', $store->id)->count())->toBe(1);
});

/* ───────────────────────── Delivery webhook ───────────────────────── */

test('the delivery webhook applies a pushed event like a poll', function () {
    [$user, $store] = twlOwner();
    $provider = twlNoestProvider($store);
    $provider->update(['webhook_token' => (string) Str::uuid()]);
    $order = twlOrder($store, $provider, 'TRK-WH-1', 'shipped');

    $this->postJson(
        route('webhooks.delivery', ['provider' => $provider->code]),
        [
            'tracking_number' => 'TRK-WH-1',
            'activity' => [
                [
                    'date' => now()->toDateTimeString(),
                    'event' => 'Livré',
                    'event_key' => 'livre',
                ],
            ],
        ],
        ['X-Delivery-Token' => $provider->webhook_token],
    )->assertOk();

    $tracking = OrderTracking::where('order_id', $order->id)->firstOrFail();

    expect($tracking->tracking_status)->toBe(OrderTrackingStatus::DELIVERED->value)
        ->and($tracking->delivered_at)->not->toBeNull()
        ->and($provider->refresh()->webhook_last_seen_at)->not->toBeNull()
        ->and(OrderTrackingHistory::where('store_id', $store->id)->where('order_tracking_id', $tracking->id)->count())->toBe(1);
});

test('the delivery webhook authenticates via the ?token= query fallback', function () {
    [$user, $store] = twlOwner();
    $provider = twlNoestProvider($store);
    $provider->update(['webhook_token' => (string) Str::uuid()]);
    $order = twlOrder($store, $provider, 'TRK-WH-4', 'shipped');

    $this->postJson(route('webhooks.delivery', ['provider' => $provider->code, 'token' => $provider->webhook_token]), [
        'tracking_number' => 'TRK-WH-4',
        'event' => 'Livré',
        'event_key' => 'livre',
    ])->assertOk();

    expect(OrderTracking::where('order_id', $order->id)->firstOrFail()->tracking_status)
        ->toBe(OrderTrackingStatus::DELIVERED->value);
});

test('the legacy token-in-path webhook endpoint keeps working transiently', function () {
    [$user, $store] = twlOwner();
    $provider = twlNoestProvider($store);
    $provider->update(['webhook_token' => (string) Str::uuid()]);
    $order = twlOrder($store, $provider, 'TRK-WH-3', 'shipped');

    $this->postJson(route('webhooks.delivery', ['provider' => $provider->webhook_token]), [
        'tracking_number' => 'TRK-WH-3',
        'event' => 'Livré',
        'event_key' => 'livre',
    ])->assertOk();

    expect(OrderTracking::where('order_id', $order->id)->firstOrFail()->tracking_status)
        ->toBe(OrderTrackingStatus::DELIVERED->value);
});

test('the delivery webhook rejects an unknown token, an unknown code and an inactive provider', function () {
    [$user, $store] = twlOwner();
    $provider = twlNoestProvider($store);
    $provider->update(['webhook_token' => (string) Str::uuid()]);
    twlOrder($store, $provider, 'TRK-WH-2', 'shipped');

    $payload = ['tracking_number' => 'TRK-WH-2', 'event' => 'Livré', 'event_key' => 'livre'];

    // Unknown token for a known code.
    $this->postJson(
        route('webhooks.delivery', ['provider' => $provider->code]),
        $payload,
        ['X-Delivery-Token' => 'missing-token'],
    )->assertNotFound();

    // Unknown code with a valid token.
    $this->postJson(
        route('webhooks.delivery', ['provider' => 'unknown-carrier']),
        $payload,
        ['X-Delivery-Token' => $provider->webhook_token],
    )->assertNotFound();

    // Unknown legacy token in the path.
    $this->postJson(route('webhooks.delivery', ['provider' => 'missing-token']), $payload)->assertNotFound();

    // Inactive provider is refused on the canonical form too.
    $provider->update(['is_active' => false]);

    $this->postJson(
        route('webhooks.delivery', ['provider' => $provider->code]),
        $payload,
        ['X-Delivery-Token' => $provider->webhook_token],
    )->assertNotFound();
});

/* ───────────────────────── Label printing ───────────────────────── */

test('the NOEST adapter exposes a label URL only when credentials exist', function () {
    [$user, $store] = twlOwner();
    $provider = twlNoestProvider($store);

    $adapter = new NoestIntegrationAdapter();

    $withCredentials = $adapter->getLabel($provider, 'TRK-LB-1');
    expect($withCredentials['ok'])->toBeTrue()
        ->and($withCredentials['url'])->toContain('/get/order/label?tracking=TRK-LB-1');

    $provider->update(['credentials' => []]);

    expect($adapter->getLabel($provider, 'TRK-LB-1')['ok'])->toBeFalse();
});

test('the merchant label proxy streams the carrier PDF with the bearer token', function () {
    [$user, $store] = twlOwner();
    $provider = twlNoestProvider($store);
    $order = twlOrder($store, $provider, 'TRK-LB-2', 'shipped');

    Http::fake([
        'noest.test/*' => Http::response('%PDF-1.7 fake label body', 200, ['Content-Type' => 'application/pdf']),
    ]);

    $response = $this->actingAs($user)
        ->withSession(['current_store_id' => $store->id])
        ->get(route('merchant.tracking.label', ['store' => $store->slug, 'tracking' => 'TRK-LB-2']));

    $response->assertOk()
        ->assertHeader('Content-Disposition', 'inline; filename="label-TRK-LB-2"')
        ->assertHeader('Content-Type', 'application/pdf');

    expect($response->getContent())->toBe('%PDF-1.7 fake label body');
});