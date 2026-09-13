<?php

use App\Domains\Shipping\Models\Carrier;
use App\Domains\Shipping\Models\CarrierPlatform;
use App\Domains\Shipping\Models\ShippingProvider;
use App\Domains\Shipping\Services\OrderShippingGateway;
use App\Models\Customer;
use App\Models\Locations\City;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use App\Models\Orders\Order;
use App\Models\Orders\OrderEvent;
use App\Models\Orders\OrderTracking;
use App\Models\Products\Product;
use App\Models\Products\ProductVariant;
use App\Models\Status;
use App\Models\Stores\Store;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(Database\Seeders\SystemStatusesSeeder::class);
});

// ---------------------------------------------------------------------------
// Per-file fixtures (Pest loads helpers globally — keep the agw* prefix).
// ---------------------------------------------------------------------------

function agwStore(): Store
{
    return Store::create([
        'user_id' => \App\Models\User::factory()->create()->id,
        'name' => 'Agw Store',
        'slug' => 'agw-'.uniqid(),
        'status' => 'active',
    ]);
}

function agwProvider(Store $store): ShippingProvider
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

function agwLocalProvider(Store $store): ShippingProvider
{
    return ShippingProvider::create([
        'store_id' => $store->id,
        'name' => 'Local Carrier',
        'code' => 'agw-local-'.substr(uniqid(), -6),
        'credentials' => [],
        'is_active' => true,
        'is_default' => true,
        'flat_rate' => 600,
    ]);
}

function agwGeography(): array
{
    $country = Country::updateOrCreate(
        ['code' => 'DZ'],
        ['name' => 'Algeria', 'arabic_name' => 'الجزائر', 'is_active' => true, 'is_cod_available' => true],
    );

    $state = State::updateOrCreate(
        ['country_id' => $country->id, 'state_code' => '16'],
        ['name' => 'Alger', 'is_active' => true, 'is_cod_available' => true],
    );

    $city = City::firstOrCreate(
        ['state_id' => $state->id, 'name' => 'Bab Ezzouar'],
        ['post_code' => '16028', 'is_active' => true, 'is_cod_available' => true],
    );

    return [$state, $city];
}

function agwOrder(Store $store, ShippingProvider $provider, string $statusKey = 'confirmed', array $opts = []): Order
{
    $status = Status::system()
        ->forType('order')
        ->where('key', $statusKey)
        ->firstOrFail();

    [$state, $city] = agwGeography();

    $customer = Customer::create([
        'store_id' => $store->id,
        'name' => $opts['customer_name'] ?? 'Agw Customer',
        'phone' => $opts['customer_phone'] ?? '0550000000',
        'status' => true,
    ]);

    $order = Order::create([
        'store_id' => $store->id,
        'customer_id' => $customer->id,
        'status_id' => $status->id,
        'number' => (new Order(['store_id' => $store->id]))->nextOrderNumber(),
        'total_amount' => 1500,
        'state_id' => $state->id,
        'city_id' => $city->id,
        'address' => $opts['address'] ?? 'Rue Nationale 12',
        'delivery_type' => $opts['delivery_type'] ?? 'home',
        'shipping_provider_id' => $provider->id,
        'payment_method' => 'cod',
        'shipping_cost' => 0,
        'weight_kg' => 0.5,
        'phone_secondary' => $opts['phone_secondary'] ?? null,
        'notes' => $opts['notes'] ?? null,
    ]);

    $product = Product::create([
        'store_id' => $store->id,
        'name' => 'Agw Product',
        'slug' => 'agw-pr-'.uniqid(),
        'sku' => 'agw-sku-'.uniqid(),
        'type' => 'variable',
        'price' => 1500,
        'is_active' => true,
    ]);

    $variant = ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Default',
        'sku' => 'agw-v-'.uniqid(),
        'price' => 1500,
        'stock' => 10,
        'is_active' => true,
    ]);

    $order->items()->create([
        'store_id' => $store->id,
        'product_variant_id' => $variant->id,
        'product_id' => $product->id,
        'quantity' => 1,
        'price' => 1500,
        'subtotal' => 1500,
    ]);

    return $order->fresh();
}

// ---------------------------------------------------------------------------
// Tests
// ---------------------------------------------------------------------------

test('send posts to the carrier first, then ships the order and creates tracking', function () {
    $store = agwStore();
    $provider = agwProvider($store);
    $order = agwOrder($store, $provider, 'pending');

    Http::fake([
        'noest.test/*' => Http::response(['success' => true, 'tracking' => 'NO20260001', 'message' => 'created']),
    ]);

    $result = app(OrderShippingGateway::class)->send(
        order: $order,
        providerId: $provider->id,
        confirmFirst: true,
    );

    Http::assertSent(fn (Request $r) => str_contains($r->url(), '/create/order'));

    expect($result['posted'])->toBeTrue()
        ->and($result['error'])->toBeNull()
        ->and($order->fresh()->status?->key)->toBe('shipped')
        ->and(OrderTracking::where('order_id', $order->id)->first()?->tracking_number)->toBe('NO20260001')
        ->and(OrderEvent::where('order_id', $order->id)->where('event_type', 'sent_to_carrier')->exists())->toBeTrue();
});

test('a carrier rejection keeps the order confirmed and creates no tracking', function () {
    $store = agwStore();
    $provider = agwProvider($store);
    $order = agwOrder($store, $provider, 'pending');

    Http::fake([
        'noest.test/*' => Http::response(['success' => false, 'message' => 'Compte suspendu']),
    ]);

    $result = app(OrderShippingGateway::class)->send(
        order: $order,
        providerId: $provider->id,
        confirmFirst: true,
    );

    expect($result['posted'])->toBeFalse()
        ->and($result['error'])->toContain('Compte suspendu')
        ->and($order->fresh()->status?->key)->toBe('confirmed')
        ->and(OrderTracking::where('order_id', $order->id)->count())->toBe(0)
        ->and(OrderEvent::where('order_id', $order->id)->where('event_type', 'sent_to_carrier')->exists())->toBeFalse();
});

test('a carrier rejection on a direct send keeps the confirmed status for retry', function () {
    $store = agwStore();
    $provider = agwProvider($store);
    $order = agwOrder($store, $provider, 'confirmed');

    Http::fake([
        'noest.test/*' => Http::response(['success' => false, 'message' => 'Commande inexistante']),
    ]);

    $result = app(OrderShippingGateway::class)->send(
        order: $order,
        providerId: $provider->id,
    );

    expect($result['posted'])->toBeFalse()
        ->and($result['error'])->toContain('Commande inexistante')
        ->and($order->fresh()->status?->key)->toBe('confirmed')
        ->and(OrderTracking::where('order_id', $order->id)->count())->toBe(0);
});

test('a carrier validation failure blocks the post before any network call', function () {
    $store = agwStore();
    $provider = agwProvider($store);
    $order = agwOrder($store, $provider, 'confirmed', ['customer_phone' => '05']);

    Http::fake();

    $result = app(OrderShippingGateway::class)->send(
        order: $order,
        providerId: $provider->id,
    );

    Http::assertNothingSent();

    expect($result['posted'])->toBeFalse()
        ->and($result['error'])->not->toBeEmpty()
        ->and($order->fresh()->status?->key)->toBe('confirmed')
        ->and(OrderTracking::where('order_id', $order->id)->count())->toBe(0);
});

test('a provider without an integration still ships locally', function () {
    $store = agwStore();
    $provider = agwLocalProvider($store);
    $order = agwOrder($store, $provider, 'confirmed');

    Http::fake();

    $result = app(OrderShippingGateway::class)->send(
        order: $order,
        providerId: $provider->id,
    );

    Http::assertNothingSent();

    expect($result['posted'])->toBeFalse()
        ->and($result['error'])->toBeNull()
        ->and($order->fresh()->status?->key)->toBe('shipped')
        ->and(OrderEvent::where('order_id', $order->id)->where('event_type', 'sent_to_carrier')->exists())->toBeTrue();
});