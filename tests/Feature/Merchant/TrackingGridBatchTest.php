<?php

use App\Domains\Shipping\Models\Carrier;
use App\Domains\Shipping\Models\CarrierPlatform;
use App\Domains\Shipping\Models\ShippingProvider;
use App\Enums\Store\OrderTrackingStatus;
use App\Enums\Store\StoreRoleEnum;
use App\Models\Customer;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Models\Orders\OrderStatusHistory;
use App\Models\Orders\OrderTracking;
use App\Models\Products\Product;
use App\Models\Products\ProductVariant;
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

function tgbOwner(): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate(StoreRoleEnum::OWNER->value, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Tracking Batch Store',
        'slug' => 'tracking-batch-'.uniqid(),
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

function tgbStatus(string $key): Status
{
    return Status::system()->forType('order')->where('key', $key)->firstOrFail();
}

function tgbProvider(Store $store, string $name): ShippingProvider
{
    return ShippingProvider::create([
        'store_id' => $store->id,
        'name' => $name,
        'code' => 'TGB'.strtoupper(substr($name, 0, 2)),
        'is_active' => true,
        'credentials' => [],
        'shipment_types_enabled' => ['delivery'],
    ]);
}

function tgbNoestProvider(Store $store): ShippingProvider
{
    $platform = CarrierPlatform::create(['name' => 'Noest B', 'slug' => 'noest-b-'.uniqid(), 'is_active' => true]);

    $carrier = Carrier::create([
        'platform_id' => $platform->id,
        'name' => 'NOEST B',
        'code' => 'noest',
        'credential_fields' => [],
        'is_active' => true,
        'supports_order_delete' => true,
    ]);

    return ShippingProvider::create([
        'store_id' => $store->id,
        'name' => 'NOEST Batch',
        'code' => 'noest',
        'carrier_id' => $carrier->id,
        'credentials' => ['api_token' => 'token-'.uniqid(), 'guid' => 'guid-'.uniqid(), 'api_base' => 'https://noest.test/api/public'],
        'is_active' => true,
        'shipment_types_enabled' => ['delivery'],
    ]);
}

function tgbOrder(Store $store, ShippingProvider $provider, string $trackingNumber, string $statusKey): Order
{
    $customer = Customer::create([
        'store_id' => $store->id,
        'name' => 'TGB '.fake()->unique()->firstName(),
        'phone' => '0560'.fake()->unique()->numerify('######'),
        'status' => true,
    ]);

    $order = Order::create([
        'store_id' => $store->id,
        'customer_id' => $customer->id,
        'status_id' => tgbStatus($statusKey)->id,
        'number' => (new Order(['store_id' => $store->id]))->nextOrderNumber(),
        'total_amount' => 900,
        'shipping_cost' => 0,
        'shipping_provider_id' => $provider->id,
        'delivery_type' => 'stopdesk',
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

function tgbVolt(array $ctx): object
{
    [$user, $store] = $ctx;
    actingAs($user)->withSession(['current_store_id' => $store->id]);

    return Volt::test('merchant.tracking.index');
}

test('the assigned-to filter narrows the grid to that membership', function () {
    [$user, $store, $membership] = tgbOwner();
    $provider = tgbProvider($store, 'Assign Co');
    $assigned = tgbOrder($store, $provider, 'TRK-AS-1', 'shipped');
    $assigned->update(['assigned_to_membership_id' => $membership->id]);
    $plain = tgbOrder($store, $provider, 'TRK-AS-2', 'shipped');

    $volt = tgbVolt([$user, $store]);
    $volt->assertSet('allMembers', fn ($rows) => collect($rows)->contains('id', (string) $membership->id));

    $volt->call('setFilter', 'assigned_to', $membership->id)
        ->assertSet('filters.assigned_to', $membership->id)
        ->assertSet('shipments', fn ($rows) => count($rows) === 1
            && collect($rows)->pluck('number')->contains($assigned->number)
            && collect($rows)->pluck('number')->doesntContain($plain->number));

    // Row map exposes the display name for the assigned_to column.
    $volt->assertSet('shipments.0.assigned_to', $user->name);

    $volt->call('setFilter', 'assigned_to', null)
        ->assertSet('shipments', fn ($rows) => count($rows) === 2);
});

test('the confirmed-by filter narrows the grid to the confirming membership', function () {
    [$user, $store, $membership] = tgbOwner();
    $provider = tgbProvider($store, 'Confirm Co');
    $handled = tgbOrder($store, $provider, 'TRK-CF-1', 'shipped');

    OrderStatusHistory::create([
        'order_id' => $handled->id,
        'status_id' => tgbStatus('confirmed')->id,
        'changed_by_membership_id' => $membership->id,
    ]);

    $plain = tgbOrder($store, $provider, 'TRK-CF-2', 'shipped');

    $volt = tgbVolt([$user, $store]);
    $volt->call('setFilter', 'confirmed_by', $membership->id)
        ->assertSet('filters.confirmed_by', $membership->id)
        ->assertSet('shipments', fn ($rows) => count($rows) === 1
            && collect($rows)->pluck('number')->contains($handled->number)
            && collect($rows)->pluck('number')->doesntContain($plain->number));

    $volt->assertSet('shipments.0.confirmed_by', $user->name);

    $volt->call('setFilter', 'confirmed_by', null)
        ->assertSet('shipments', fn ($rows) => count($rows) === 2);
});

test('the grid row map carries the new columns (state, assigned_to, confirmed_by, notes, flags)', function () {
    [$user, $store, $membership] = tgbOwner();
    $provider = tgbProvider($store, 'Row Co');
    $order = tgbOrder($store, $provider, 'TRK-ROW-1', 'shipped');
    $order->update(['assigned_to_membership_id' => $membership->id]);

    $volt = tgbVolt([$user, $store]);

    $volt->assertSet('shipments.0.id', (string) $order->id)
        ->assertSet('shipments.0.can_edit_order', true)
        ->assertSet('shipments.0.can_cancel_shipment', false)
        ->assertSet('shipments.0.carrier_supports_api_notes', false);

    // Terminal handled by the editor only for non-carrier providers; plain
    // providers (no carrier) must not expose cancel-shipment.
    expect($volt->get('shipments')[0])->toHaveKey('assigned_to')
        ->toHaveKey('confirmed_by')
        ->toHaveKey('state')
        ->toHaveKey('latest_note')
        ->toHaveKey('tracking_id');
});

test('editing is refused for a terminal (delivered/returned) shipment', function () {
    [$user, $store, $membership] = tgbOwner();
    $provider = tgbProvider($store, 'Edit Co');
    $delivered = tgbOrder($store, $provider, 'TRK-ED-1', 'delivered');

    $volt = tgbVolt([$user, $store]);
    $volt->call('openEditModal', (string) $delivered->id)
        ->assertSet('showEditModal', false)
        ->assertDispatched('swal:toast', fn ($name, $params) => ($params[0]['icon'] ?? null) === 'error');
});

test('openEditModal loads the order into the edit form for in-flight shipments', function () {
    [$user, $store, $membership] = tgbOwner();
    $user->update(['name' => 'Edit Agent']);
    $provider = tgbProvider($store, 'Edit Co 2');
    $order = tgbOrder($store, $provider, 'TRK-ED-2', 'in_transit');
    $supplier = Product::create([
        'store_id' => $store->id,
        'name' => 'Batch Product',
        'slug' => 'batch-product-'.uniqid(),
        'sku' => 'batch-sku-'.uniqid(),
        'type' => 'variable',
        'price' => 500,
        'is_active' => true,
    ]);
    $variant = ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $supplier->id,
        'name' => 'Default',
        'sku' => 'batch-v-'.uniqid(),
        'price' => 500,
        'stock' => 100,
        'is_active' => true,
    ]);
    OrderItem::create([
        'store_id' => $store->id,
        'order_id' => $order->id,
        'product_id' => $supplier->id,
        'product_variant_id' => $variant->id,
        'quantity' => 1,
        'price' => 500,
        'subtotal' => 500,
    ]);

    $volt = tgbVolt([$user, $store]);
    $volt->call('openEditModal', (string) $order->id)
        ->assertSet('showEditModal', true)
        ->assertSet('editingOrderId', (string) $order->id)
        ->assertSet('form.customer_name', $order->customer->name)
        ->assertSet('form.items.0.product_variant_id', $variant->id);
});

test('submitEdit persists the changes and refreshes the grid', function () {
    [$user, $store, $membership] = tgbOwner();
    $user->update(['name' => 'Edit Agent']);
    $provider = tgbProvider($store, 'Edit Co 3');
    $order = tgbOrder($store, $provider, 'TRK-ED-3', 'in_transit');
    $variant = ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => Product::create([
            'store_id' => $store->id,
            'name' => 'Batch Product 2',
            'slug' => 'batch-product-2-'.uniqid(),
            'sku' => 'batch-sku-2-'.uniqid(),
            'type' => 'variable',
            'price' => 700,
            'is_active' => true,
        ])->id,
        'name' => 'Default',
        'sku' => 'batch-v-2-'.uniqid(),
        'price' => 700,
        'stock' => 100,
        'is_active' => true,
    ]);
    OrderItem::create([
        'store_id' => $store->id,
        'order_id' => $order->id,
        'product_id' => $variant->product_id,
        'product_variant_id' => $variant->id,
        'quantity' => 1,
        'price' => 700,
        'subtotal' => 700,
    ]);

    $volt = tgbVolt([$user, $store]);
    $volt->call('openEditModal', (string) $order->id);
    $newPhone = '0560'.fake()->unique()->numerify('######');
    $volt->set('form.customer_phone', $newPhone)
        ->set('form.items.0.quantity', 2)
        ->call('submitEdit')
        ->assertSet('showEditModal', false)
        ->assertDispatched('swal:toast', fn ($name, $params) => ($params[0]['icon'] ?? null) === 'success');

    expect($order->refresh()->customer->phone)->toBe($newPhone)
        ->and($order->items()->first()->quantity)->toBe(2)
        ->and((float) $order->total_amount)->toBe(1400.0);
});

test('deleteOrder soft-deletes the shipment and removes it from the grid', function () {
    [$user, $store, $membership] = tgbOwner();
    $provider = tgbProvider($store, 'Delete Co');
    $order = tgbOrder($store, $provider, 'TRK-DL-1', 'shipped');

    $volt = tgbVolt([$user, $store]);
    $volt->call('openDrawer', (string) $order->id)
        ->assertSet('drawerOrderId', (string) $order->id)
        ->call('deleteOrder', (string) $order->id)
        ->assertSet('drawerOrderId', null)
        ->assertDispatched('swal:toast', fn ($name, $params) => ($params[0]['icon'] ?? null) === 'success')
        ->assertSet('shipments', fn ($rows) => count($rows) === 0);

    expect(Order::withTrashed()->find($order->id)->trashed())->toBeTrue();
});

test('cancelShipment deletes the carrier shipment and reverts the order to confirmed', function () {
    [$user, $store, $membership] = tgbOwner();
    $provider = tgbNoestProvider($store);
    $order = tgbOrder($store, $provider, 'TRK-CX-1', 'shipped');

    $tracking = OrderTracking::where('order_id', $order->id)->firstOrFail();

    Http::fake([
        'noest.test/*' => Http::response(['success' => true, 'message' => 'deleted']),
    ]);

    $volt = tgbVolt([$user, $store]);
    $volt->assertSet('shipments.0.can_cancel_shipment', true)
        ->call('cancelShipment', (string) $order->id)
        ->assertDispatched('swal:toast', fn ($name, $params) => ($params[0]['icon'] ?? null) === 'success')
        ->assertSet('shipments', fn ($rows) => count($rows) === 0);

    expect($order->refresh()->status_key)->toBe('confirmed')
        ->and($tracking->refresh()->tracking_number)->toBeNull()
        ->and($tracking->carrier_status)->toBe('cancelled')
        ->and($tracking->tracking_status)->toBeNull();
});

test('cancelShipment is refused when the carrier has no delete API', function () {
    [$user, $store, $membership] = tgbOwner();
    $provider = tgbProvider($store, 'Plain Cancel Co');
    $order = tgbOrder($store, $provider, 'TRK-CX-2', 'in_transit');
    $tracking = OrderTracking::where('order_id', $order->id)->firstOrFail();

    Http::fake();

    $volt = tgbVolt([$user, $store]);
    $volt->assertSet('shipments.0.can_cancel_shipment', false)
        ->call('cancelShipment', (string) $order->id)
        ->assertDispatched('swal:toast', fn ($name, $params) => ($params[0]['icon'] ?? null) === 'error');

    expect($order->refresh()->status_key)->toBe('in_transit')
        ->and($tracking->refresh()->tracking_number)->not->toBeNull();
});

test('a validated (delivered-confirmed) shipment cannot be cancelled', function () {
    [$user, $store, $membership] = tgbOwner();
    $provider = tgbNoestProvider($store);
    $order = tgbOrder($store, $provider, 'TRK-CX-3', 'shipped');
    $tracking = OrderTracking::where('order_id', $order->id)->firstOrFail();
    $tracking->update(['carrier_validated_at' => now()]);

    $volt = tgbVolt([$user, $store]);
    $volt->assertSet('shipments.0.can_cancel_shipment', false)
        ->call('cancelShipment', (string) $order->id)
        ->assertDispatched('swal:toast', fn ($name, $params) => ($params[0]['icon'] ?? null) === 'error');

    expect($order->refresh()->status_key)->toBe('shipped')
        ->and($tracking->refresh()->tracking_number)->not->toBeNull();
});