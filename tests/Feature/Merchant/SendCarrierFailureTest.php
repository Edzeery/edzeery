<?php

use App\Domains\Shipping\Models\Carrier;
use App\Domains\Shipping\Models\CarrierPlatform;
use App\Domains\Shipping\Models\ShippingProvider;
use App\Models\Customer;
use App\Models\Locations\City;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Models\Orders\OrderTracking;
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
// Per-file fixtures (keep the scf* prefix to avoid global collisions).
// ---------------------------------------------------------------------------

function scfUser(): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate('owner', 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Send Failure Store',
        'slug' => 'scf-'.uniqid(),
        'status' => 'active',
    ]);

    $membership = StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'invited_by' => $user->id,
        'is_active' => true,
        'role' => 'owner',
    ]);

    return [$user, $store, $membership];
}

function scfProvider(Store $store): ShippingProvider
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
        'carrier_platform_id' => $platform->id,
        'carrier_id' => $carrier->id,
        'credentials' => [
            'api_token' => 'tok-'.uniqid(),
            'guid' => 'guid-'.uniqid(),
            'api_base' => 'https://noest.test/api/public',
        ],
        'is_active' => true,
    ]);
}

function scfGeography(): array
{
    $country = Country::firstOrCreate(
        ['code' => 'DZ'],
        ['name' => 'Algeria', 'arabic_name' => 'الجزائر', 'is_active' => true],
    );

    $state = State::firstOrCreate(
        ['country_id' => $country->id, 'state_code' => '16'],
        ['name' => 'Alger', 'is_active' => true],
    );

    $city = City::firstOrCreate(
        ['state_id' => $state->id, 'name' => 'Bab Ezzouar'],
        ['post_code' => '16028', 'is_active' => true],
    );

    return [$state, $city];
}

function scfOrder(Store $store, ShippingProvider $provider, string $statusKey = 'confirmed'): Order
{
    $status = Status::system()
        ->forType('order')
        ->where('key', $statusKey)
        ->firstOrFail();

    [$state, $city] = scfGeography();

    $order = Order::create([
        'store_id' => $store->id,
        'customer_id' => null,
        'status_id' => $status->id,
        'number' => (new Order(['store_id' => $store->id]))->nextOrderNumber(),
        'total_amount' => 1500,
        'shipping_cost' => 0,
        'state_id' => $state->id,
        'city_id' => $city->id,
        'address' => 'Rue Nationale 12',
        'delivery_type' => 'home',
        'shipping_provider_id' => $provider->id,
    ]);

    $customer = Customer::create([
        'store_id' => $store->id,
        'name' => 'Scf Customer',
        'phone' => '0550000000',
        'status' => true,
    ]);
    $order->update(['customer_id' => $customer->id]);

    $product = Product::create([
        'store_id' => $store->id,
        'name' => 'Scf Product',
        'slug' => 'scf-pr-'.uniqid(),
        'sku' => 'scf-sku-'.uniqid(),
        'type' => 'variable',
        'price' => 1500,
        'is_active' => true,
    ]);

    $variant = ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Default',
        'sku' => 'scf-v-'.uniqid(),
        'price' => 1500,
        'stock' => 10,
        'is_active' => true,
    ]);

    OrderItem::create([
        'store_id' => $store->id,
        'order_id' => $order->id,
        'product_variant_id' => $variant->id,
        'quantity' => 1,
        'price' => 1500,
        'subtotal' => 1500,
    ]);

    return $order->fresh();
}

function scfToastTitle(array $params): ?string
{
    $payload = ($params[0] ?? null) && is_array($params[0]) ? $params[0] : $params;

    return $payload['title'] ?? null;
}

function scfToastIcon(array $params): ?string
{
    $payload = ($params[0] ?? null) && is_array($params[0]) ? $params[0] : $params;

    return $payload['icon'] ?? null;
}

// ---------------------------------------------------------------------------
// Tests
// ---------------------------------------------------------------------------

test('confirm-and-send keeps the order confirmed when the carrier rejects it', function () {
    [$user, $store, $membership] = scfUser();
    $provider = scfProvider($store);
    $order = scfOrder($store, $provider, 'pending');

    Http::fake([
        'noest.test/*' => Http::response(['success' => false, 'message' => 'Compte suspendu']),
    ]);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.orders.index')
        ->set('confirmOrderId', $order->id)
        ->set('confirmProviderId', (string) $provider->id)
        ->call('submitConfirmAndSend')
        ->assertSet('showConfirmModal', false)
        ->assertDispatched('swal:toast', fn ($name, $params) => scfToastIcon($params) === 'warning'
            && str_contains(scfToastTitle($params) ?? '', 'Compte suspendu'));

    expect($order->fresh()->status?->key)->toBe('confirmed')
        ->and(OrderTracking::where('order_id', $order->id)->count())->toBe(0);
});

test('direct-send keeps the order confirmed when the carrier rejects it', function () {
    [$user, $store, $membership] = scfUser();
    $provider = scfProvider($store);
    $order = scfOrder($store, $provider, 'confirmed');

    Http::fake([
        'noest.test/*' => Http::response(['success' => false, 'message' => 'Commande inexistante']),
    ]);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.orders.index')
        ->call('sendConfirmedOrder', $order->id)
        ->assertDispatched('swal:toast', fn ($name, $params) => scfToastIcon($params) === 'warning'
            && str_contains(scfToastTitle($params) ?? '', 'Commande inexistante'));

    expect($order->fresh()->status?->key)->toBe('confirmed')
        ->and(OrderTracking::where('order_id', $order->id)->count())->toBe(0);
});

test('bulk send counts a carrier rejection as skipped with the carrier message', function () {
    [$user, $store, $membership] = scfUser();
    $provider = scfProvider($store);
    $order = scfOrder($store, $provider, 'confirmed');

    Http::fake([
        'noest.test/*' => Http::response(['success' => false, 'message' => 'Compte suspendu']),
    ]);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.orders.index')
        ->set('selectedOrders', [$order->id])
        ->call('confirmBulkSend')
        ->assertDispatched('swal:toast', function ($name, $params) use ($order) {
            $title = scfToastTitle($params) ?? '';

            return scfToastIcon($params) === 'warning'
                && str_contains($title, (string) $order->number)
                && str_contains($title, 'Compte suspendu');
        });

    expect($order->fresh()->status?->key)->toBe('confirmed')
        ->and(OrderTracking::where('order_id', $order->id)->count())->toBe(0);
});