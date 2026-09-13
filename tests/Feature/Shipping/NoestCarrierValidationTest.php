<?php

use App\Domains\Shipping\Adapters\NoestIntegrationAdapter;
use App\Domains\Shipping\Models\Carrier;
use App\Domains\Shipping\Models\CarrierPlatform;
use App\Domains\Shipping\Models\ShippingProvider;
use App\Domains\Shipping\Models\StopdeskPoint;
use App\Models\Customer;
use App\Models\Locations\City;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use App\Models\Orders\Order;
use App\Models\Products\Product;
use App\Models\Products\ProductVariant;
use App\Models\Stores\Store;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

// ---------------------------------------------------------------------------
// Per-file fixtures (Pest loads helpers globally — keep the ncv* prefix).
// ---------------------------------------------------------------------------

function ncvStore(): Store
{
    return Store::create([
        'user_id' => \App\Models\User::factory()->create()->id,
        'name' => 'Ncv Store',
        'slug' => 'ncv-'.uniqid(),
        'status' => 'active',
    ]);
}

function ncvProvider(Store $store, ?string $token = null, ?string $guid = null): ShippingProvider
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
            'api_token' => $token ?? 'tok-'.uniqid(),
            'guid' => $guid ?? 'guid-'.uniqid(),
        ],
        'is_active' => true,
    ]);
}

function ncvGeography(): array
{
    $country = Country::updateOrCreate(
        ['code' => 'DZ'],
        [
            'name' => 'Algeria',
            'is_active' => true,
            'is_cod_available' => true,
        ],
    );

    $state = State::updateOrCreate(
        ['country_id' => $country->id, 'state_code' => '16'],
        [
            'name' => 'Alger',
            'is_active' => true,
            'is_cod_available' => true,
        ],
    );

    $city = City::firstOrCreate(
        ['state_id' => $state->id, 'name' => 'Bab Ezzouar'],
        [
            'post_code' => '16028',
            'is_active' => true,
            'is_cod_available' => true,
        ],
    );

    return [$state, $city];
}

function ncvOrder(Store $store, ShippingProvider $provider, array $extra = []): Order
{
    [$state, $city] = ncvGeography();

    $customer = Customer::create([
        'store_id' => $store->id,
        'name' => $extra['customer_name'] ?? 'Ncv Customer',
        'phone' => $extra['customer_phone'] ?? '0550000000',
        'status' => true,
    ]);

    $order = Order::create(array_merge([
        'store_id' => $store->id,
        'customer_id' => $customer->id,
        'number' => 'NCV-'.substr(uniqid(), -6),
        'total_amount' => 1500,
        'state_id' => $state->id,
        'city_id' => $city->id,
        'address' => $extra['address'] ?? 'Rue Nationale 12',
        'delivery_type' => $extra['delivery_type'] ?? 'home',
        'stopdesk_point_id' => $extra['stopdesk_point_id'] ?? null,
        'shipping_provider_id' => $provider->id,
        'payment_method' => 'cod',
        'shipping_cost' => 0,
        'weight_kg' => 0.5,
        'notes' => $extra['notes'] ?? null,
        'phone_secondary' => $extra['phone_secondary'] ?? null,
    ], array_diff_key($extra, array_fill_keys(['address', 'delivery_type', 'stopdesk_point_id', 'notes', 'phone_secondary', 'customer_name', 'customer_phone'], true))));

    $product = Product::create([
        'store_id' => $store->id,
        'name' => 'Ncv Product',
        'slug' => 'ncv-pr-'.uniqid(),
        'sku' => 'ncv-sku-'.uniqid(),
        'type' => 'variable',
        'price' => 1500,
        'is_active' => true,
    ]);

    $variant = ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Default',
        'sku' => 'ncv-v-'.uniqid(),
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

test('a complete order validates as ready for the carrier', function () {
    $store = ncvStore();
    $provider = ncvProvider($store);
    $order = ncvOrder($store, $provider);

    $result = app(NoestIntegrationAdapter::class)->validateForCarrier($provider, $order);

    expect($result['validated'])->toBeTrue()
        ->and($result['errors'])->toBe([]);
});

test('flags a phone that is shorter than 9 digits and an invalid secondary phone', function () {
    $store = ncvStore();
    $provider = ncvProvider($store);
    $order = ncvOrder($store, $provider, ['customer_phone' => '0550', 'phone_secondary' => '12']);

    $result = app(NoestIntegrationAdapter::class)->validateForCarrier($provider, $order);

    expect($result['validated'])->toBeFalse()
        ->and($result['errors']['phone'])->not->toBeEmpty()
        ->and($result['errors']['phone_2'])->not->toBeEmpty();
});

test('accepts normalized phone formats the carrier treats as 9-10 digits', function () {
    $store = ncvStore();
    $provider = ncvProvider($store);
    $order = ncvOrder($store, $provider, ['customer_phone' => '+213 550 12 34 56']);

    $result = app(NoestIntegrationAdapter::class)->validateForCarrier($provider, $order);

    expect($result['validated'])->toBeTrue();
});

test('flags a client name over 255 characters', function () {
    $store = ncvStore();
    $provider = ncvProvider($store);
    $order = ncvOrder($store, $provider, ['customer_name' => str_repeat('ع', 256)]);

    $result = app(NoestIntegrationAdapter::class)->validateForCarrier($provider, $order);

    expect($result['validated'])->toBeFalse()
        ->and($result['errors']['client'])->not->toBeEmpty();
});

test('flags missing API credentials', function () {
    $store = ncvStore();
    $provider = ncvProvider($store, '', '');
    $order = ncvOrder($store, $provider);

    $result = app(NoestIntegrationAdapter::class)->validateForCarrier($provider, $order);

    expect($result['validated'])->toBeFalse()
        ->and($result['errors']['credentials'])->not->toBeEmpty();
});

test('flags a stopdesk order without an external station code', function () {
    $store = ncvStore();
    $provider = ncvProvider($store);
    [$state, $city] = ncvGeography();

    $point = StopdeskPoint::create([
        'store_id' => $store->id,
        'shipping_provider_id' => $provider->id,
        'state_id' => $state->id,
        'city_id' => $city->id,
        'name' => 'Desk Sans Code',
        'address' => 'Rue 2',
        'external_code' => null,
        'is_active' => true,
    ]);

    $order = ncvOrder($store, $provider, [
        'delivery_type' => 'stopdesk',
        'stopdesk_point_id' => $point->id,
    ]);

    $result = app(NoestIntegrationAdapter::class)->validateForCarrier($provider, $order);

    expect($result['validated'])->toBeFalse()
        ->and($result['errors']['station_code'])->not->toBeEmpty();
});

test('flags a wilaya code outside the 1-58 range', function () {
    $store = ncvStore();
    $provider = ncvProvider($store);
    [$state, $city] = ncvGeography();

    $outOfRange = State::updateOrCreate(
        ['country_id' => $state->country_id, 'state_code' => '99'],
        ['name' => 'Inconnu', 'is_active' => true, 'is_cod_available' => true],
    );
    $cityOut = City::firstOrCreate(['state_id' => $outOfRange->id, 'name' => 'Ville 99'], ['post_code' => '99000', 'is_active' => true]);

    $order = ncvOrder($store, $provider, [
        'state_id' => $outOfRange->id,
        'city_id' => $cityOut->id,
    ]);

    $result = app(NoestIntegrationAdapter::class)->validateForCarrier($provider, $order);

    expect($result['validated'])->toBeFalse()
        ->and($result['errors']['wilaya'])->not->toBeEmpty();
});

test('flags an overlong address and overlong remarks', function () {
    $store = ncvStore();
    $provider = ncvProvider($store);
    $order = ncvOrder($store, $provider, [
        'address' => str_repeat('a', 256),
        'notes' => str_repeat('b', 256),
    ]);

    $result = app(NoestIntegrationAdapter::class)->validateForCarrier($provider, $order);

    expect($result['validated'])->toBeFalse()
        ->and($result['errors']['address'])->not->toBeEmpty()
        ->and($result['errors']['remarque'])->not->toBeEmpty();
});