<?php

use App\Domains\Order\Models\Order;
use App\Domains\Order\Services\OrderService;
use App\Domains\Shipping\Models\DeliveryPriceList;
use App\Domains\Shipping\Models\DeliveryRate;
use App\Domains\Shipping\Models\DeliveryRateListState;
use App\Domains\Shipping\Models\ShippingProvider;
use App\Domains\Shipping\Models\ShippingRate;
use App\Domains\Shipping\Services\ShippingCostCalculator;
use App\Models\Locations\City;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use App\Models\Products\Product;
use App\Models\Products\ProductVariant;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use Database\Seeders\PlansSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\StoreRolesAndPermissionsSeeder;
use Database\Seeders\SystemStatusesSeeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(StoreRolesAndPermissionsSeeder::class);
    $this->seed(SystemStatusesSeeder::class);
    $this->seed(PlansSeeder::class);

    [$user, $store, $membership] = osdpEnv();
    $this->user = $user;
    $this->store = $store;
    $this->membership = $membership;

    [$country, $state, $city] = osdpGeography();
    $this->country = $country;
    $this->state = $state;
    $this->city = $city;

    $this->calculator = app(ShippingCostCalculator::class);
});

function osdpEnv(): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate('owner', 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Osdp Store',
        'slug' => 'osdp-'.uniqid(),
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

function osdpGeography(): array
{
    $country = Country::firstOrCreate(
        ['code' => 'DZ'],
        ['name' => 'Algeria', 'arabic_name' => 'الجزائر', 'is_active' => true],
    );

    $state = State::firstOrCreate(
        ['country_id' => $country->id, 'state_code' => '16'],
        ['name' => 'Alger', 'is_active' => true, 'is_cod_available' => true],
    );

    $city = City::firstOrCreate(
        ['state_id' => $state->id, 'name' => 'Bab Ezzouar'],
        ['post_code' => '16028', 'is_active' => true],
    );

    return [$country, $state, $city];
}

function osdpProvider(Store $store, string $name = 'Yalidine', bool $default = true, ?float $flatRate = null): ShippingProvider
{
    return ShippingProvider::create([
        'store_id' => $store->id,
        'name' => $name,
        'code' => Str::slug($name).'-'.uniqid(),
        'credentials' => [],
        'is_active' => true,
        'is_default' => $default,
        'flat_rate' => $flatRate,
    ]);
}

function osdpProduct(Store $store, string $name, float $price = 900): Product
{
    $product = Product::create([
        'store_id' => $store->id,
        'name' => $name,
        'slug' => Str::slug($name).'-'.uniqid(),
        'sku' => 'osdp-'.uniqid(),
        'price' => $price,
        'is_active' => true,
    ]);

    ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Default',
        'sku' => 'osdp-v-'.uniqid(),
        'price' => $price,
        'stock' => 10,
    ]);

    return $product;
}

// ————— Stopdesk pricing —————

test('stopdesk resolves the requested provider office cost', function () {
    $provider = osdpProvider($this->store);

    DeliveryRate::create([
        'store_id' => $this->store->id,
        'shipping_provider_id' => $provider->id,
        'state_id' => $this->state->id,
        'home_cost' => 900,
        'office_cost' => 350,
        'is_active' => true,
    ]);

    $result = $this->calculator->calculate(
        $this->store, $this->state->id, null, 0, [], (string) $provider->id, ShippingCostCalculator::DELIVERY_STOPDESK,
    );

    expect($result['cost'])->toBe(350.0)
        ->and($result['method'])->toBe('rate')
        ->and($result['available'])->toBeTrue()
        ->and($result['provider_name'])->toBe('Yalidine')
        ->and($result['source_type'])->toBe('company');
});

test('stopdesk without a provider falls back to the default provider office cost', function () {
    $default = osdpProvider($this->store, 'Default', true);
    $other = osdpProvider($this->store, 'Other', false);

    DeliveryRate::create([
        'store_id' => $this->store->id,
        'shipping_provider_id' => $other->id,
        'state_id' => $this->state->id,
        'office_cost' => 300,
        'is_active' => true,
    ]);

    DeliveryRate::create([
        'store_id' => $this->store->id,
        'shipping_provider_id' => $default->id,
        'state_id' => $this->state->id,
        'office_cost' => 250,
        'is_active' => true,
    ]);

    $result = $this->calculator->calculate(
        $this->store, $this->state->id, null, 0, [], null, ShippingCostCalculator::DELIVERY_STOPDESK,
    );

    expect($result['provider_name'])->toBe('Default')
        ->and($result['cost'])->toBe(250.0);
});

test('stopdesk without an office price is office_unavailable, never free', function () {
    $provider = osdpProvider($this->store);

    DeliveryRate::create([
        'store_id' => $this->store->id,
        'shipping_provider_id' => $provider->id,
        'state_id' => $this->state->id,
        'home_cost' => 900,
        'is_active' => true,
    ]);

    $result = $this->calculator->calculate(
        $this->store, $this->state->id, null, 0, [], (string) $provider->id, ShippingCostCalculator::DELIVERY_STOPDESK,
    );

    expect($result['method'])->toBe('office_unavailable')
        ->and($result['available'])->toBeFalse()
        ->and($result['is_free'])->toBeFalse()
        ->and($result['cost'])->toBe(0);
});

test('stopdesk ignores list prices but falls back to the legacy shipping rate', function () {
    $provider = osdpProvider($this->store);
    $product = osdpProduct($this->store, 'Desk Product');

    $list = DeliveryPriceList::create([
        'store_id' => $this->store->id,
        'name' => 'List',
        'is_active' => true,
    ]);
    $list->products()->attach($product->id);

    DeliveryRateListState::create([
        'delivery_price_list_id' => $list->id,
        'state_id' => $this->state->id,
        'home_cost' => 650,
        'office_cost' => 300,
    ]);

    ShippingRate::create([
        'store_id' => $this->store->id,
        'shipping_provider_id' => $provider->id,
        'state_id' => $this->state->id,
        'cost' => 400,
        'is_active' => true,
    ]);

    $result = $this->calculator->calculate(
        $this->store, $this->state->id, null, 0, [$product->id], (string) $provider->id, ShippingCostCalculator::DELIVERY_STOPDESK,
    );

    // The list office price (300) must never surface for stopdesk; the legacy
    // announced rate (400) is the stopdesk fallback.
    expect($result['cost'])->toBe(400.0)
        ->and($result['provider_name'])->toBe('Yalidine')
        ->and($result['source_type'])->toBe('company');
});

test('stopdesk free_above applies even without a base office cost', function () {
    $provider = osdpProvider($this->store);

    DeliveryRate::create([
        'store_id' => $this->store->id,
        'shipping_provider_id' => $provider->id,
        'state_id' => $this->state->id,
        'office_cost' => null,
        'free_above' => 5000,
        'is_active' => true,
    ]);

    $free = $this->calculator->calculate(
        $this->store, $this->state->id, null, 6000, [], (string) $provider->id, ShippingCostCalculator::DELIVERY_STOPDESK,
    );

    expect($free['is_free'])->toBeTrue()
        ->and($free['method'])->toBe('free')
        ->and($free['cost'])->toBe(0);

    $closed = $this->calculator->calculate(
        $this->store, $this->state->id, null, 3000, [], (string) $provider->id, ShippingCostCalculator::DELIVERY_STOPDESK,
    );

    expect($closed['method'])->toBe('office_unavailable');
});

// ————— Home + provider scoping —————

test('home price list wins over the chosen company provider', function () {
    $provider = osdpProvider($this->store);
    $product = osdpProduct($this->store, 'Listed Product');

    DeliveryRate::create([
        'store_id' => $this->store->id,
        'shipping_provider_id' => $provider->id,
        'state_id' => $this->state->id,
        'home_cost' => 1000,
        'is_active' => true,
    ]);

    $list = DeliveryPriceList::create([
        'store_id' => $this->store->id,
        'name' => 'List',
        'is_active' => true,
    ]);
    $list->products()->attach($product->id);

    DeliveryRateListState::create([
        'delivery_price_list_id' => $list->id,
        'state_id' => $this->state->id,
        'home_cost' => 650,
        'office_cost' => 300,
    ]);

    $result = $this->calculator->calculate(
        $this->store, $this->state->id, null, 0, [$product->id], (string) $provider->id, ShippingCostCalculator::DELIVERY_HOME,
    );

    expect($result['cost'])->toBe(650.0)
        ->and($result['source_type'])->toBe('price_list')
        ->and($result['provider_name'])->toBe('List');
});

test('home with a provider scopes announced rates to that provider', function () {
    $first = osdpProvider($this->store, 'First', false);
    $second = osdpProvider($this->store, 'Second', true);

    DeliveryRate::create([
        'store_id' => $this->store->id,
        'shipping_provider_id' => $first->id,
        'state_id' => $this->state->id,
        'home_cost' => 900,
        'is_active' => true,
    ]);

    DeliveryRate::create([
        'store_id' => $this->store->id,
        'shipping_provider_id' => $second->id,
        'state_id' => $this->state->id,
        'home_cost' => 1100,
        'is_active' => true,
    ]);

    $result = $this->calculator->calculate(
        $this->store, $this->state->id, null, 0, [], (string) $first->id, ShippingCostCalculator::DELIVERY_HOME,
    );

    expect($result['provider_name'])->toBe('First')
        ->and($result['cost'])->toBe(900.0);
});

test('provider id selects that provider flat rate', function () {
    $first = osdpProvider($this->store, 'First', true, 600);
    $second = osdpProvider($this->store, 'Second', false, 800);

    $result = $this->calculator->calculate(
        $this->store, $this->state->id, null, 0, [], (string) $second->id, ShippingCostCalculator::DELIVERY_HOME,
    );

    expect($result['method'])->toBe('provider_flat')
        ->and($result['cost'])->toBe(800.0)
        ->and($result['source_type'])->toBe('company_flat');
});

// ————— OrderService::createManual —————

test('createManual persists the stopdesk office cost as shipping_cost', function () {
    $provider = osdpProvider($this->store);
    $product = osdpProduct($this->store, 'Desk Item', 800);

    DeliveryRate::create([
        'store_id' => $this->store->id,
        'shipping_provider_id' => $provider->id,
        'state_id' => $this->state->id,
        'office_cost' => 350,
        'is_active' => true,
    ]);

    $variant = $product->variants()->first();

    $order = app(OrderService::class)->createManual([
        'delivery_type' => 'stopdesk',
        'shipping_provider_id' => $provider->id,
        'state_id' => $this->state->id,
        'city_id' => $this->city->id,
        'total_amount' => 800,
        'items' => [
            ['product_variant_id' => $variant->id, 'product_id' => $product->id, 'quantity' => 1, 'price' => 800],
        ],
    ], $this->membership);

    expect((float) $order->fresh()->shipping_cost)->toBe(350.0)
        ->and($order->delivery_type)->toBe('stopdesk');
});

test('createManual stopdesk without an office price resolves to zero cost', function () {
    $provider = osdpProvider($this->store);
    $product = osdpProduct($this->store, 'Desk Item 2', 800);

    $variant = $product->variants()->first();

    $order = app(OrderService::class)->createManual([
        'delivery_type' => 'stopdesk',
        'shipping_provider_id' => $provider->id,
        'state_id' => $this->state->id,
        'total_amount' => 800,
        'items' => [
            ['product_variant_id' => $variant->id, 'product_id' => $product->id, 'quantity' => 1, 'price' => 800],
        ],
    ], $this->membership);

    expect((float) $order->fresh()->shipping_cost)->toBe(0.0);
});

test('createManual home order uses the covering price list', function () {
    $provider = osdpProvider($this->store);
    $product = osdpProduct($this->store, 'Listed Item', 900);

    $list = DeliveryPriceList::create([
        'store_id' => $this->store->id,
        'name' => 'List',
        'is_active' => true,
    ]);
    $list->products()->attach($product->id);

    DeliveryRateListState::create([
        'delivery_price_list_id' => $list->id,
        'state_id' => $this->state->id,
        'home_cost' => 650,
    ]);

    $variant = $product->variants()->first();

    $order = app(OrderService::class)->createManual([
        'shipping_provider_id' => $provider->id,
        'state_id' => $this->state->id,
        'total_amount' => 900,
        'items' => [
            ['product_variant_id' => $variant->id, 'product_id' => $product->id, 'quantity' => 1, 'price' => 900],
        ],
    ], $this->membership);

    expect((float) $order->fresh()->shipping_cost)->toBe(650.0);
});

test('createManual persists the delivery rider id (rider partner survives create)', function () {
    $rider = \App\Domains\Shipping\Models\DeliveryRider::create([
        'store_id' => $this->store->id,
        'name' => 'Riad',
        'phone' => '0550000000',
        'vehicle_type' => 'motorcycle',
        'is_active' => true,
    ]);

    $product = osdpProduct($this->store, 'Rider Item', 300);
    $variant = $product->variants()->first();

    $order = app(OrderService::class)->createManual([
        'delivery_type' => 'home',
        'delivery_rider_id' => $rider->id,
        'total_amount' => 300,
        'items' => [
            ['product_variant_id' => $variant->id, 'product_id' => $product->id, 'quantity' => 1, 'price' => 300],
        ],
    ], $this->membership);

    $fresh = $order->fresh();

    expect($fresh->delivery_rider_id)->toBe($rider->id)
        ->and($fresh->shipping_provider_id)->toBeNull();
});

test('createManual standardizes total_amount (subtotal + shipping, pre-discount) and percent discounts apply to the goods subtotal only', function () {
    $provider = osdpProvider($this->store);
    $product = osdpProduct($this->store, 'Pct Item', 800);

    DeliveryRate::create([
        'store_id' => $this->store->id,
        'shipping_provider_id' => $provider->id,
        'state_id' => $this->state->id,
        'home_cost' => 200,
        'is_active' => true,
    ]);

    $variant = $product->variants()->first();

    $order = app(OrderService::class)->createManual([
        'shipping_provider_id' => $provider->id,
        'state_id' => $this->state->id,
        'total_amount' => 800,
        'discount_type' => 'percent',
        'discount_value' => 10,
        'items' => [
            ['product_variant_id' => $variant->id, 'product_id' => $product->id, 'quantity' => 1, 'price' => 800],
        ],
    ], $this->membership);

    $fresh = $order->fresh();

    expect((float) $fresh->shipping_cost)->toBe(200.0)
        ->and((float) $fresh->total_amount)->toBe(1000.0)
        ->and((float) $fresh->discount_amount)->toBe(80.0)
        ->and((float) $fresh->grand_total)->toBe(920.0);
});