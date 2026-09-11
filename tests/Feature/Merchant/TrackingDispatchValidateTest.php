<?php

use App\Domains\Order\Services\OrderTrackingService;
use App\Domains\Shipping\Models\Carrier;
use App\Domains\Shipping\Models\CarrierPlatform;
use App\Domains\Shipping\Models\ShippingProvider;
use App\Enums\Store\StoreRoleEnum;
use App\Models\Customer;
use App\Models\Orders\Order;
use App\Models\Orders\OrderTracking;
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

function tdvUser(string $role = StoreRoleEnum::OWNER->value): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate($role, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Tdv Store',
        'slug' => 'tdv-'.uniqid(),
        'status' => 'active',
    ]);

    $membership = StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'invited_by' => $user->id,
        'is_active' => true,
        'role' => $role,
    ]);

    return [$user, $store, $membership];
}

function tdvProvider(Store $store): ShippingProvider
{
    $platform = CarrierPlatform::updateOrCreate(
        ['slug' => 'noest'],
        ['name' => 'NOEST', 'is_active' => true],
    );

    $carrier = Carrier::updateOrCreate(
        ['code' => 'noest'],
        [
            'platform_id' => $platform->id,
            'name' => 'NOEST',
            'is_active' => true,
        ],
    );

    return ShippingProvider::create([
        'store_id' => $store->id,
        'name' => 'NOEST DZ',
        'code' => 'noest',
        'carrier_id' => $carrier->id,
        'credentials' => [
            'api_token' => 'tok-'.uniqid(),
            'guid' => 'guid-'.uniqid(),
            'api_base' => 'https://noest.test/api/public',
        ],
        'is_active' => true,
    ]);
}

function tdvOrder(Store $store, ShippingProvider $provider, string $trackingNumber, array $trackingExtra = []): Order
{
    $customer = Customer::create([
        'store_id' => $store->id,
        'name' => 'Tdv Customer',
        'phone' => '0552'.fake()->unique()->numerify('######'),
        'status' => true,
    ]);

    $order = Order::create([
        'store_id' => $store->id,
        'customer_id' => $customer->id,
        'number' => (new Order(['store_id' => $store->id]))->nextOrderNumber(),
        'total_amount' => 1500,
        'shipping_provider_id' => $provider->id,
        'shipping_cost' => 0,
    ]);

    OrderTracking::create(array_merge([
        'store_id' => $store->id,
        'order_id' => $order->id,
        'shipping_provider_id' => $provider->id,
        'tracking_number' => $trackingNumber,
        'shipped_at' => now(),
    ], $trackingExtra));

    return $order->fresh();
}

test('the drawer exposes the handover section for a validate-capable carrier shipment', function () {
    [$user, $store] = tdvUser();
    $provider = tdvProvider($store);
    $order = tdvOrder($store, $provider, 'NOEST123');

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.tracking.index')
        ->call('openDrawer', $order->id)
        ->assertSee(__('order_flow.validate_shipment_title'))
        ->assertSee(__('order_flow.validate_scan_label'));

    $drawer = Volt::test('merchant.tracking.index')
        ->call('openDrawer', $order->id)
        ->get('drawerTracking');

    expect($drawer['carrier_supports_validation'])->toBeTrue()
        ->and($drawer['carrier_validated_at'])->toBeNull();
});

test('validateShipment hands the shipment to the carrier and persists carrier_validated_at', function () {
    [$user, $store] = tdvUser();
    $provider = tdvProvider($store);
    $order = tdvOrder($store, $provider, 'NOEST123');

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Http::fake(['noest.test/*' => Http::response(['success' => true])]);

    Volt::test('merchant.tracking.index')
        ->call('openDrawer', $order->id)
        ->call('validateShipment', $order->id)
        ->assertDispatched('swal:toast');

    $tracking = app(OrderTrackingService::class)->currentTracking($order->fresh());

    expect($tracking->isCarrierValidated())->toBeTrue()
        ->and($tracking->carrier_validation_error)->toBeNull();
});

test('validateShipment persists carrier_validation_error when the carrier rejects', function () {
    [$user, $store] = tdvUser();
    $provider = tdvProvider($store);
    $order = tdvOrder($store, $provider, 'NOEST123');

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Http::fake(['noest.test/*' => Http::response(['success' => false, 'message' => 'Stock insuffisant'])]);

    Volt::test('merchant.tracking.index')
        ->call('openDrawer', $order->id)
        ->call('validateShipment', $order->id)
        ->assertDispatched('swal:toast');

    $tracking = app(OrderTrackingService::class)->currentTracking($order->fresh());

    expect($tracking->isCarrierValidated())->toBeFalse()
        ->and($tracking->carrier_validation_error)->toBe('Stock insuffisant');
});

test('validateShipmentFromBarcode refuses a tracking number that does not match the shipment', function () {
    [$user, $store] = tdvUser();
    $provider = tdvProvider($store);
    $order = tdvOrder($store, $provider, 'NOEST123');

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Http::fake();

    Volt::test('merchant.tracking.index')
        ->call('openDrawer', $order->id)
        ->call('validateShipmentFromBarcode', 'WRONG-TRACKING')
        ->assertDispatched('swal:toast');

    Http::assertNothingSent();

    expect(app(OrderTrackingService::class)->currentTracking($order->fresh())->isCarrierValidated())->toBeFalse();
});

test('validateShipmentFromBarcode validates when the scanned tracking matches', function () {
    [$user, $store] = tdvUser();
    $provider = tdvProvider($store);
    $order = tdvOrder($store, $provider, 'NOEST123');

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Http::fake(['noest.test/*' => Http::response(['success' => true])]);

    Volt::test('merchant.tracking.index')
        ->call('openDrawer', $order->id)
        ->call('validateShipmentFromBarcode', 'NOEST123')
        ->assertDispatched('swal:toast');

    expect(app(OrderTrackingService::class)->currentTracking($order->fresh())->isCarrierValidated())->toBeTrue();
});

test('the handover section is replaced by a badge once the shipment is validated', function () {
    [$user, $store] = tdvUser();
    $provider = tdvProvider($store);
    $order = tdvOrder($store, $provider, 'NOEST123', [
        'carrier_validated_at' => now(),
        'carrier_validated_by_membership_id' => null,
    ]);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.tracking.index')
        ->call('openDrawer', $order->id)
        ->assertSee(__('order_flow.shipment_validated_badge'))
        ->assertDontSee(__('order_flow.validate_scan_label'));
});

test('validateShipment is refused without the order.dispatch_validate permission', function () {
    [$user, $store] = tdvUser(StoreRoleEnum::STAFF->value);
    $provider = tdvProvider($store);
    $order = tdvOrder($store, $provider, 'NOEST123');

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Http::fake();

    Volt::test('merchant.tracking.index')
        ->call('openDrawer', $order->id)
        ->call('validateShipment', $order->id)
        ->assertStatus(403);

    Http::assertNothingSent();

    expect(app(OrderTrackingService::class)->currentTracking($order->fresh())->isCarrierValidated())->toBeFalse();
});