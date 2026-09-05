<?php

use App\Domains\Shipping\Models\ShippingProvider;
use App\Domains\Shipping\Models\StopdeskPoint;
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

function dsoUser(string $storeRole = 'owner'): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate($storeRole, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Dso Store',
        'slug' => 'dso-'.uniqid(),
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

function dsoProvider(Store $store): ShippingProvider
{
    return ShippingProvider::create([
        'store_id' => $store->id,
        'name' => 'Dso Carrier',
        'code' => 'dso-local',
        'credentials' => [],
        'is_active' => true,
        'is_default' => true,
        'flat_rate' => 600,
    ]);
}

function dsoGeography(): array
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

function dsoVariant(Store $store): ProductVariant
{
    $product = Product::create([
        'store_id' => $store->id,
        'name' => 'Dso Product',
        'slug' => 'dso-pr-'.uniqid(),
        'sku' => 'dso-sku-'.uniqid(),
        'type' => 'variable',
        'price' => 400,
        'is_active' => true,
    ]);

    return ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Default',
        'sku' => 'dso-v-'.uniqid(),
        'price' => 400,
        'stock' => 10,
        'is_active' => true,
    ]);
}

/**
 * $this->dispatch('swal:toast', ['icon' => …, 'title' => …]) stores the
 * payload as a single-element array ([0 => […]]); unwrap it for assertions.
 */
function dsoToastTitle(array $params): ?string
{
    $payload = ($params[0] ?? null) && is_array($params[0]) ? $params[0] : $params;

    return $payload['title'] ?? null;
}

function dsoToastIcon(array $params): ?string
{
    $payload = ($params[0] ?? null) && is_array($params[0]) ? $params[0] : $params;

    return $payload['icon'] ?? null;
}

/**
 * Creates an order at the given status. Ready-to-ship by default (linked
 * customer with name+phone, home address, geography, one item, provider).
 * Options to simulate gaps:
 *   link_customer  => false  → no customer row (missing name/phone)
 *   customer_name  => null   → blank customer name
 *   address        => null   → missing home address
 *   stopdesk_point_id => filled → stopdesk delivery (address not required)
 *   with_provider  => false  → no provider and no rider (missing partner)
 *   with_items     => false  → no order items
 */
function dsoOrder(Store $store, string $statusKey = 'confirmed', array $opts = []): Order
{
    $status = Status::system()
        ->forType('order')
        ->where('key', $statusKey)
        ->firstOrFail();

    [$state, $city] = dsoGeography();

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
        'shipping_provider_id' => ($opts['with_provider'] ?? true) ? dsoProvider($store)->id : null,
    ]);

    if ($opts['link_customer'] ?? true) {
        $customer = Customer::create([
            'store_id' => $store->id,
            'name' => $opts['customer_name'] ?? 'Dso Customer',
            'phone' => '0550'.fake()->unique()->numerify('######'),
            'status' => true,
        ]);
        $order->update(['customer_id' => $customer->id]);
    }

    if ($opts['with_items'] ?? true) {
        OrderItem::create([
            'store_id' => $store->id,
            'order_id' => $order->id,
            'product_variant_id' => dsoVariant($store)->id,
            'product_id' => null,
            'quantity' => 1,
            'price' => 400,
            'subtotal' => 400,
        ]);
    }

    return $order->fresh();
}

test('action is denied without order.manage permission', function () {
    [$user, $store, $membership] = dsoUser(StoreRoleEnum::STAFF->value);
    $order = dsoOrder($store);

    expect(canStore(\App\Enums\Store\StorePermissionEnum::ORDER_MANAGE->value))->toBeFalse();

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.orders.index')
        ->call('sendConfirmedOrder', $order->id)
        ->assertStatus(403);

    expect($order->fresh()->status?->key)->toBe('confirmed');
});

test('refuses a pending order without auto-confirming it', function () {
    [$user, $store, $membership] = dsoUser(StoreRoleEnum::OWNER->value);
    $order = dsoOrder($store, 'pending');

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.orders.index')
        ->call('sendConfirmedOrder', $order->id)
        ->assertDispatched('swal:toast', fn ($name, $params) => dsoToastTitle($params) === __('order_flow.send_requires_confirmation'));

    expect($order->fresh()->status?->key)->toBe('pending');
});

test('direct-sends a confirmed order to the carrier', function () {
    [$user, $store, $membership] = dsoUser(StoreRoleEnum::OWNER->value);
    $order = dsoOrder($store, 'confirmed');

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.orders.index')
        ->call('sendConfirmedOrder', $order->id)
        ->assertDispatched('swal:toast', fn ($name, $params) => dsoToastIcon($params) === 'success');

    expect($order->fresh()->status?->key)->toBe('shipped')
        ->and(OrderEvent::where('order_id', $order->id)->where('event_type', 'sent_to_carrier')->exists())->toBeTrue();
});

test('direct-sends a preparing order to the carrier', function () {
    [$user, $store, $membership] = dsoUser(StoreRoleEnum::OWNER->value);
    $order = dsoOrder($store, 'preparing');

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.orders.index')
        ->call('sendConfirmedOrder', $order->id)
        ->assertDispatched('swal:toast', fn ($name, $params) => dsoToastIcon($params) === 'success');

    expect($order->fresh()->status?->key)->toBe('shipped');
});

test('blocks the send and names the missing field when the address is absent', function () {
    [$user, $store, $membership] = dsoUser(StoreRoleEnum::OWNER->value);
    $order = dsoOrder($store, 'confirmed', ['address' => null]);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.orders.index')
        ->call('sendConfirmedOrder', $order->id)
        ->assertDispatched('swal:toast', fn ($name, $params) => dsoToastTitle($params) === __('order_flow.send_missing_fields', ['fields' => __('merchant_panel.address')]));

    expect($order->fresh()->status?->key)->toBe('confirmed');
});

test('lists every missing field when several are absent', function () {
    [$user, $store, $membership] = dsoUser(StoreRoleEnum::OWNER->value);
    $order = dsoOrder($store, 'confirmed', [
        'link_customer' => false,
        'with_items' => false,
        'with_provider' => false,
    ]);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.orders.index')
        ->call('sendConfirmedOrder', $order->id)
        ->assertDispatched('swal:toast', function ($name, $params) {
            $title = dsoToastTitle($params) ?? '';

            return str_contains($title, __('merchant_panel.customer_name'))
                && str_contains($title, __('merchant_panel.customer_phone'))
                && str_contains($title, __('merchant_panel.items'))
                && str_contains($title, __('order_flow.confirm_partner'));
        });

    expect($order->fresh()->status?->key)->toBe('confirmed');
});

test('accepts a stopdesk order without a home address', function () {
    [$user, $store, $membership] = dsoUser(StoreRoleEnum::OWNER->value);
    $provider = dsoProvider($store);
    $point = StopdeskPoint::create([
        'store_id' => $store->id,
        'shipping_provider_id' => $provider->id,
        'name' => 'Dso Stopdesk',
        'address' => 'Point 1',
        'is_active' => true,
    ]);
    $order = dsoOrder($store, 'confirmed', [
        'address' => null,
        'delivery_type' => 'stopdesk',
        'stopdesk_point_id' => $point->id,
    ]);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.orders.index')
        ->call('sendConfirmedOrder', $order->id)
        ->assertDispatched('swal:toast', fn ($name, $params) => dsoToastIcon($params) === 'success');

    expect($order->fresh()->status?->key)->toBe('shipped');
});