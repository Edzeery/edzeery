<?php

use App\Domains\Shipping\Models\DeliveryRider;
use App\Domains\Shipping\Models\ShippingProvider;
use App\Enums\Store\StoreRoleEnum;
use App\Models\Customer;
use App\Models\Locations\City;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use App\Models\Orders\Order;
use App\Models\Orders\OrderEvent;
use App\Models\Orders\OrderItem;
use App\Models\Products\Product;
use App\Models\Products\ProductVariant;
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

// ---------------------------------------------------------------------------
// Self-contained fixtures (each Pest file owns its helper functions).
// ---------------------------------------------------------------------------

function bscUser(string $storeRole = 'owner'): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate($storeRole, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Bsc Store',
        'slug' => 'bsc-' . uniqid(),
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

function bscGeography(): array
{
    $country = Country::firstOrCreate(
        ['code' => 'DZ'],
        ['name' => 'Algeria', 'arabic_name' => 'الجزائر', 'is_active' => true],
    );

    $state = State::firstOrCreate(
        ['country_id' => $country->id, 'state_code' => '01'],
        ['name' => 'Adrar', 'arabic_name' => 'أدرار', 'is_active' => true],
    );

    $city = City::firstOrCreate(
        ['state_id' => $state->id, 'name' => 'Adrar Centre'],
        ['post_code' => '01000', 'is_active' => true],
    );

    return [$state, $city];
}

function bscProvider(Store $store, string $name): ShippingProvider
{
    return ShippingProvider::create([
        'store_id' => $store->id,
        'name' => $name,
        'code' => 'bsc-' . strtolower(\Illuminate\Support\Str::slug($name)) . '-' . substr(uniqid(), -4),
        'credentials' => [],
        'is_active' => true,
        'is_default' => false,
        'flat_rate' => 600,
    ]);
}

function bscRider(Store $store): DeliveryRider
{
    return DeliveryRider::create([
        'store_id' => $store->id,
        'name' => 'Bsc Rider',
        'phone' => '0551' . substr(uniqid(), -6),
        'is_active' => true,
    ]);
}

function bscVariant(Store $store): ProductVariant
{
    $product = Product::create([
        'store_id' => $store->id,
        'name' => 'Bsc Product',
        'slug' => 'bsc-pr-' . uniqid(),
        'sku' => 'bsc-sku-' . uniqid(),
        'type' => 'variable',
        'price' => 400,
        'is_active' => true,
    ]);

    return ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Default',
        'sku' => 'bsc-v-' . uniqid(),
        'price' => 400,
        'stock' => 10,
        'is_active' => true,
    ]);
}

/**
 * Ready-to-ship order at the given status. Options mirror the direct-send
 * spec: with_provider=false → no provider; address=null → missing address.
 */
function bscOrder(Store $store, string $statusKey = 'confirmed', array $opts = []): Order
{
    $status = Status::system()
        ->forType('order')
        ->where('key', $statusKey)
        ->firstOrFail();

    [$state, $city] = bscGeography();

    $order = Order::create([
        'store_id' => $store->id,
        'customer_id' => null,
        'status_id' => $status->id,
        'number' => (new Order(['store_id' => $store->id]))->nextOrderNumber(),
        'total_amount' => 400,
        'shipping_cost' => 0,
        'state_id' => $state->id,
        'city_id' => $city->id,
        'address' => array_key_exists('address', $opts) ? $opts['address'] : 'Rue des Cedres',
        'delivery_type' => $opts['delivery_type'] ?? 'home',
        'stopdesk_point_id' => $opts['stopdesk_point_id'] ?? null,
        'shipping_provider_id' => ($opts['with_provider'] ?? true) ? bscProvider($store, 'Bsc Carrier')->id : null,
    ]);

    $customer = Customer::create([
        'store_id' => $store->id,
        'name' => $opts['customer_name'] ?? 'Bsc Customer',
        'phone' => '0551' . fake()->unique()->numerify('######'),
        'status' => true,
    ]);
    $order->update(['customer_id' => $customer->id]);

    OrderItem::create([
        'store_id' => $store->id,
        'order_id' => $order->id,
        'product_variant_id' => bscVariant($store)->id,
        'product_id' => null,
        'quantity' => 1,
        'price' => 400,
        'subtotal' => 400,
    ]);

    return $order->fresh();
}

/**
 * dsoToast-style unwrappers: Livewire nests swal:toast params under [0].
 */
function bscToastTitle(array $params): ?string
{
    $payload = ($params[0] ?? null) && is_array($params[0]) ? $params[0] : $params;

    return $payload['title'] ?? null;
}

function bscToastIcon(array $params): ?string
{
    $payload = ($params[0] ?? null) && is_array($params[0]) ? $params[0] : $params;

    return $payload['icon'] ?? null;
}

// ---------------------------------------------------------------------------
// Tests
// ---------------------------------------------------------------------------

test('bulk send modal is denied without order.manage permission', function () {
    [$user, $store, $membership] = bscUser(StoreRoleEnum::STAFF->value);
    bscOrder($store);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    expect(canStore(\App\Enums\Store\StorePermissionEnum::ORDER_MANAGE->value))->toBeFalse();

    Volt::test('merchant.orders.index')
        ->call('openBulkSendModal')
        ->assertStatus(403);
});

test('bulk send modal warns when no orders are selected', function () {
    [$user, $store, $membership] = bscUser(StoreRoleEnum::OWNER->value);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.orders.index')
        ->set('selectedOrders', [])
        ->call('openBulkSendModal')
        ->assertDispatched('swal:toast', fn ($name, $params) => bscToastTitle($params) === __('merchant.no_orders_selected'))
        ->assertSet('showBulkSendModal', false);
});

test('groups selected orders by their own carrier (provider, fallback rider, unassigned)', function () {
    [$user, $store, $membership] = bscUser(StoreRoleEnum::OWNER->value);
    $alpha = bscProvider($store, 'Alpha Carrier');
    $beta = bscProvider($store, 'Beta Carrier');

    $a1 = bscOrder($store);
    $a1->update(['shipping_provider_id' => $alpha->id]);
    $a2 = bscOrder($store);
    $a2->update(['shipping_provider_id' => $alpha->id]);
    $b1 = bscOrder($store);
    $b1->update(['shipping_provider_id' => $beta->id]);

    $riderOrder = bscOrder($store, 'confirmed', ['with_provider' => false]);
    $riderOrder->update(['delivery_rider_id' => bscRider($store)->id]);

    $unassigned = bscOrder($store, 'confirmed', ['with_provider' => false]);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.orders.index')
        ->set('selectedOrders', [$a1->id, $a2->id, $b1->id, $riderOrder->id, $unassigned->id])
        ->call('openBulkSendModal')
        ->assertSet('showBulkSendModal', true)
        ->assertSet('bulkSendSummary.0.name', 'Alpha Carrier')
        ->assertSet('bulkSendSummary.0.count', 2)
        ->assertSet('bulkSendSummary.1.name', 'Beta Carrier')
        ->assertSet('bulkSendSummary.1.count', 1)
        ->assertSet('bulkSendSummary.2.name', __('order_flow.bulk_send_rider'))
        ->assertSet('bulkSendSummary.2.count', 1)
        ->assertSet('bulkSendSummary.3.name', __('order_flow.bulk_send_unassigned'))
        ->assertSet('bulkSendSummary.3.count', 1);
});

test('sends each confirmed order to its own carrier with a per-carrier summary toast', function () {
    [$user, $store, $membership] = bscUser(StoreRoleEnum::OWNER->value);
    $alpha = bscProvider($store, 'Alpha Carrier');
    $beta = bscProvider($store, 'Beta Carrier');

    $a1 = bscOrder($store);
    $a1->update(['shipping_provider_id' => $alpha->id]);
    $a2 = bscOrder($store);
    $a2->update(['shipping_provider_id' => $alpha->id]);
    $b1 = bscOrder($store);
    $b1->update(['shipping_provider_id' => $beta->id]);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.orders.index')
        ->set('selectedOrders', [$a1->id, $a2->id, $b1->id])
        ->call('openBulkSendModal')
        ->call('confirmBulkSend')
        ->assertDispatched('swal:toast', function ($name, $params) {
            $title = bscToastTitle($params) ?? '';

            return bscToastIcon($params) === 'success'
                && str_contains($title, 'Alpha Carrier (2)')
                && str_contains($title, 'Beta Carrier (1)');
        });

    expect($a1->fresh()->status?->key)->toBe('shipped')
        ->and($a2->fresh()->status?->key)->toBe('shipped')
        ->and($b1->fresh()->status?->key)->toBe('shipped')
        ->and(OrderEvent::where('order_id', $a1->id)->where('event_type', 'sent_to_carrier')->exists())->toBeTrue()
        ->and(OrderEvent::where('order_id', $b1->id)->where('event_type', 'sent_to_carrier')->exists())->toBeTrue();
});

test('skips pending orders without auto-confirming them during bulk send', function () {
    [$user, $store, $membership] = bscUser(StoreRoleEnum::OWNER->value);
    $alpha = bscProvider($store, 'Alpha Carrier');

    $ready = bscOrder($store);
    $ready->update(['shipping_provider_id' => $alpha->id]);
    $pending = bscOrder($store, 'pending');
    $pending->update(['shipping_provider_id' => $alpha->id]);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    $test = Volt::test('merchant.orders.index')
        ->set('selectedOrders', [$ready->id, $pending->id])
        ->call('openBulkSendModal')
        ->call('confirmBulkSend');

    $test->assertDispatched('swal:toast', function ($name, $params) {
        $title = bscToastTitle($params) ?? '';

        return bscToastIcon($params) === 'warning'
            && str_contains($title, __('order_flow.bulk_send_skipped', ['count' => 1]));
    });

    expect($ready->fresh()->status?->key)->toBe('shipped')
        ->and($pending->fresh()->status?->key)->toBe('pending');
});

test('skips orders with missing readiness fields during bulk send', function () {
    [$user, $store, $membership] = bscUser(StoreRoleEnum::OWNER->value);
    $alpha = bscProvider($store, 'Alpha Carrier');

    $ready = bscOrder($store);
    $ready->update(['shipping_provider_id' => $alpha->id]);
    $incomplete = bscOrder($store, 'confirmed', ['address' => null]);
    $incomplete->update(['shipping_provider_id' => $alpha->id]);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.orders.index')
        ->set('selectedOrders', [$ready->id, $incomplete->id])
        ->call('openBulkSendModal')
        ->call('confirmBulkSend')
        ->assertDispatched('swal:toast', function ($name, $params) {
            $title = bscToastTitle($params) ?? '';

            return bscToastIcon($params) === 'warning'
                && str_contains($title, __('order_flow.bulk_send_skipped', ['count' => 1]));
        });

    expect($ready->fresh()->status?->key)->toBe('shipped')
        ->and($incomplete->fresh()->status?->key)->toBe('confirmed');
});

test('sends a rider-only order to its rider during bulk send', function () {
    [$user, $store, $membership] = bscUser(StoreRoleEnum::OWNER->value);

    $riderOrder = bscOrder($store, 'confirmed', ['with_provider' => false]);
    $riderOrder->update(['delivery_rider_id' => bscRider($store)->id]);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.orders.index')
        ->set('selectedOrders', [$riderOrder->id])
        ->call('openBulkSendModal')
        ->assertSet('bulkSendSummary.0.name', __('order_flow.bulk_send_rider'))
        ->call('confirmBulkSend')
        ->assertDispatched('swal:toast', function ($name, $params) {
            $title = bscToastTitle($params) ?? '';

            return bscToastIcon($params) === 'success'
                && str_contains($title, sprintf('%s (1)', __('order_flow.bulk_send_rider')));
        });

    expect($riderOrder->fresh()->status?->key)->toBe('shipped');
});

test('skips orders with no carrier at all during bulk send', function () {
    [$user, $store, $membership] = bscUser(StoreRoleEnum::OWNER->value);
    $alpha = bscProvider($store, 'Alpha Carrier');

    $ready = bscOrder($store);
    $ready->update(['shipping_provider_id' => $alpha->id]);
    $unassigned = bscOrder($store, 'confirmed', ['with_provider' => false]);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.orders.index')
        ->set('selectedOrders', [$ready->id, $unassigned->id])
        ->call('openBulkSendModal')
        ->call('confirmBulkSend')
        ->assertDispatched('swal:toast', function ($name, $params) {
            $title = bscToastTitle($params) ?? '';

            return bscToastIcon($params) === 'warning'
                && str_contains($title, __('order_flow.bulk_send_skipped', ['count' => 1]));
        });

    expect($ready->fresh()->status?->key)->toBe('shipped')
        ->and($unassigned->fresh()->status?->key)->toBe('confirmed');
});