<?php

use App\Domains\Shipping\Adapters\NoestIntegrationAdapter;
use App\Domains\Shipping\Models\Carrier;
use App\Domains\Shipping\Models\CarrierPlatform;
use App\Domains\Shipping\Models\ShippingProvider;
use App\Domains\Shipping\Models\StopdeskPoint;
use App\Domains\Shipping\Services\CarrierOrderPostService;
use App\Domains\Shipping\Services\StopdeskOfficeSync;
use App\Models\Customer;
use App\Models\Locations\City;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use App\Models\Orders\Order;
use App\Models\Products\Product;
use App\Models\Products\ProductVariant;
use App\Models\Stores\Store;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Cache::flush();
});

function noestStore(): Store
{
    return Store::create([
        'user_id' => \App\Models\User::factory()->create()->id,
        'name' => 'Noest Store',
        'slug' => 'noest-'.uniqid(),
        'status' => 'active',
    ]);
}

function noestProvider(Store $store, ?string $token = null, ?string $guid = null): ShippingProvider
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

function noestGeography(): array
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

function noestOrder(Store $store, ShippingProvider $provider, array $extra = []): Order
{
    [$state, $city] = noestGeography();

    $customer = Customer::create([
        'store_id' => $store->id,
        'name' => 'Noest Customer',
        'phone' => '0550000000',
        'status' => true,
    ]);

    $order = Order::create(array_merge([
        'store_id' => $store->id,
        'customer_id' => $customer->id,
        'number' => 'NOEST-'.substr(uniqid(), -6),
        'total_amount' => 1500,
        'state_id' => $state->id,
        'city_id' => $city->id,
        'address' => 'Rue Nationale 12',
        'delivery_type' => 'home',
        'shipping_provider_id' => $provider->id,
        'payment_method' => 'cod',
        'shipping_cost' => 0,
        'weight_kg' => 0.5,
    ], $extra));

    $product = Product::create([
        'store_id' => $store->id,
        'name' => 'Noest Product',
        'slug' => 'noest-pr-'.uniqid(),
        'sku' => 'noest-sku-'.uniqid(),
        'type' => 'variable',
        'price' => 1500,
        'is_active' => true,
    ]);

    $variant = ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Default',
        'sku' => 'noest-v-'.uniqid(),
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

test('offices are mapped from desks, filtered by wilaya and sorted by commune match', function () {
    $store = noestStore();
    $provider = noestProvider($store);

    [$state16, $city16] = noestGeography();

    $country = Country::where('code', 'DZ')->first();
    $state31 = State::create([
        'country_id' => $country->id,
        'state_code' => '31',
        'name' => 'Oran',
        'is_active' => true,
        'is_cod_available' => true,
    ]);

    Http::fake([
        'app.noest-dz.com/*' => Http::response([
            ['code' => '16', 'name' => 'Alger Desk 1', 'commune' => 'Dar El Beida', 'address' => 'Rue 1'],
            ['code' => '16', 'name' => 'Alger Desk 2', 'commune' => 'Bab Ezzouar', 'address' => 'Rue 2', 'phones' => ['0550000000']],
            ['code' => '31', 'name' => 'Oran Desk', 'commune' => 'Bir El Djir', 'address' => 'Rue 3'],
        ]),
    ]);

    $offices = app(NoestIntegrationAdapter::class)->offices($provider, $state16, $city16);

    expect($offices)->toHaveCount(2)
        ->and($offices[0]['external_code'])->toBe('16') // Bab Ezzouar first (exact commune match)
        ->and($offices[0]['city'])->toBe('Bab Ezzouar')
        ->and($offices[0]['phone'])->toBe('0550000000')
        ->and($offices[1]['external_code'])->toBe('16');

    expect(Cache::has("carrier:noest:desks:{$store->id}:{$provider->id}"))->toBeTrue();
});

test('StopdeskOfficeSync persists offices and is idempotent', function () {
    $store = noestStore();
    $provider = noestProvider($store);
    [$state16, $city16] = noestGeography();

    Http::fake([
        'app.noest-dz.com/*' => Http::response([
            ['code' => '16', 'name' => 'Alger Desk 1', 'commune' => 'Bab Ezzouar', 'address' => 'Rue 1'],
        ]),
    ]);

    $sync = app(StopdeskOfficeSync::class);

    $first = $sync->sync($provider, $state16, $city16);
    $second = $sync->sync($provider, $state16, $city16);

    expect($first['synced'])->toBeTrue()
        ->and($first['created'])->toBe(1)
        ->and($second['created'])->toBe(0)
        ->and($second['existing'])->toBe(1);

    $point = StopdeskPoint::where('shipping_provider_id', $provider->id)->first();

    expect($point)->not->toBeNull()
        ->and($point->external_code)->toBe('16')
        ->and($point->state_id)->toBe($state16->id)
        ->and($point->city_id)->toBe($city16->id)
        ->and($point->is_active)->toBeTrue();
});

test('createOrder posts the NOEST payload and CarrierOrderPostService records tracking', function () {
    $store = noestStore();
    $provider = noestProvider($store);
    [$state16, $city16] = noestGeography();

    $point = StopdeskPoint::create([
        'store_id' => $store->id,
        'shipping_provider_id' => $provider->id,
        'state_id' => $state16->id,
        'city_id' => $city16->id,
        'name' => 'Desk Bab Ezzouar',
        'address' => 'Rue 2',
        'external_code' => '16',
        'is_active' => true,
    ]);

    $order = noestOrder($store, $provider, [
        'delivery_type' => 'stopdesk',
        'stopdesk_point_id' => $point->id,
        'phone_secondary' => '0560111222',
        'notes' => 'Vérifier avant livraison',
    ]);

    Http::fake([
        'app.noest-dz.com/*' => Http::response([
            'success' => true,
            'tracking' => 'NO20260001',
            'message' => 'created',
        ]),
    ]);

    $tracking = app(CarrierOrderPostService::class)->postToCarrier($order);

    Http::assertSent(fn (Request $r) => str_contains($r->url(), '/create/order')
        && $r['station_code'] === '16'
        && $r['stop_desk'] === 1
        && (int) $r['wilaya_id'] === 16
        && $r['commune'] === 'Bab Ezzouar'
        && $r['reference'] === $order->number
        && $r['montant'] === 1500.0
        && $r['phone_2'] === '0560111222'
        && $r['remarque'] === 'Vérifier avant livraison'
        && $r['client'] === 'Noest Customer');

    expect($tracking)->not->toBeNull()
        ->and($tracking->tracking_number)->toBe('NO20260001')
        ->and($tracking->carrier_status)->toBe('created')
        ->and($tracking->shipping_provider_id)->toBe($provider->id)
        ->and($tracking->carrier_label)->toContain('/get/order/label?tracking=NO20260001')
        ->and($tracking->carrier_raw['tracking'])->toBe('NO20260001')
        ->and($tracking->last_synced_at)->not->toBeNull();
});

test('createOrder for a home delivery omits the desk fields', function () {
    $store = noestStore();
    $provider = noestProvider($store);

    $order = noestOrder($store, $provider, ['delivery_type' => 'home']);

    Http::fake([
        'app.noest-dz.com/*' => Http::response([
            'success' => true,
            'tracking' => 'NO20260002',
        ]),
    ]);

    app(CarrierOrderPostService::class)->postToCarrier($order->fresh());

    Http::assertSent(fn (Request $r) => $r['stop_desk'] === 0
        && $r['station_code'] === ''
        && ! isset($r['remarque'])
        && ! isset($r['phone_2']));
});

test('posting to a carrier without credentials throws a RuntimeException', function () {
    $store = noestStore();
    $provider = noestProvider($store, '', '');

    $order = noestOrder($store, $provider, ['delivery_type' => 'home']);

    expect(fn () => app(CarrierOrderPostService::class)->postToCarrier($order))
        ->toThrow(RuntimeException::class);
});