<?php

use App\Domains\Shipping\Models\DeliveryPriceList;
use App\Domains\Shipping\Models\DeliveryRate;
use App\Domains\Shipping\Models\DeliveryRateCity;
use App\Domains\Shipping\Models\DeliveryRateListCity;
use App\Domains\Shipping\Models\DeliveryRateListState;
use App\Domains\Shipping\Models\ShippingProvider;
use App\Domains\Shipping\Models\ShippingRate;
use App\Domains\Shipping\Services\ShippingCostCalculator;
use App\Models\Locations\City;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use App\Models\Products\Product;
use App\Models\Stores\Store;
use App\Models\User;
use Database\Seeders\PlansSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\StoreRolesAndPermissionsSeeder;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(StoreRolesAndPermissionsSeeder::class);
    $this->seed(PlansSeeder::class);

    $this->user = User::factory()->create();
    $this->store = Store::create([
        'user_id' => $this->user->id,
        'name' => 'Test Store',
        'slug' => 'test-store-'.uniqid(),
        'status' => 'active',
    ]);

    $this->country = Country::create([
        'name' => 'Algeria',
        'code' => 'DZ',
        'is_active' => true,
        'is_cod_available' => true,
    ]);

    $this->state = State::create([
        'country_id' => $this->country->id,
        'state_code' => '16',
        'name' => 'Alger',
        'is_active' => true,
        'is_cod_available' => true,
    ]);

    $this->city = City::create([
        'state_id' => $this->state->id,
        'name' => 'Bab Ezzouar',
        'post_code' => '16028',
        'is_active' => true,
        'is_cod_available' => true,
    ]);

    $this->calculator = app(ShippingCostCalculator::class);
});

test('returns free when no rates configured', function () {
    $result = $this->calculator->calculate($this->store);

    expect($result['available'])->toBeTrue();
    expect($result['is_free'])->toBeTrue();
    expect($result['cost'])->toBe(0);
    expect($result['method'])->toBe('free');
});

test('returns provider flat rate when configured', function () {
    ShippingProvider::create([
        'store_id' => $this->store->id,
        'name' => 'Yalidine',
        'code' => 'yalidine',
        'credentials' => [],
        'is_active' => true,
        'is_default' => true,
        'flat_rate' => 600,
    ]);

    $result = $this->calculator->calculate($this->store);

    expect($result['method'])->toBe('provider_flat');
    expect($result['cost'])->toBe(600.0);
    expect($result['is_free'])->toBeFalse();
    expect($result['provider_name'])->toBe('Yalidine');
});

test('city rate takes precedence over state rate', function () {
    $provider = ShippingProvider::create([
        'store_id' => $this->store->id,
        'name' => 'Test',
        'code' => 'test',
        'credentials' => [],
        'is_active' => true,
    ]);

    ShippingRate::create([
        'store_id' => $this->store->id,
        'shipping_provider_id' => $provider->id,
        'state_id' => $this->state->id,
        'city_id' => null,
        'cost' => 800,
        'is_active' => true,
    ]);

    ShippingRate::create([
        'store_id' => $this->store->id,
        'shipping_provider_id' => $provider->id,
        'state_id' => $this->state->id,
        'city_id' => $this->city->id,
        'cost' => 400,
        'is_active' => true,
    ]);

    $result = $this->calculator->calculate($this->store, $this->state->id, $this->city->id);

    expect($result['cost'])->toBe(400.0);
    expect($result['method'])->toBe('rate');
});

test('state rate used when no city rate exists', function () {
    $provider = ShippingProvider::create([
        'store_id' => $this->store->id,
        'name' => 'Test',
        'code' => 'test',
        'credentials' => [],
        'is_active' => true,
    ]);

    ShippingRate::create([
        'store_id' => $this->store->id,
        'shipping_provider_id' => $provider->id,
        'state_id' => $this->state->id,
        'city_id' => null,
        'cost' => 700,
        'is_active' => true,
    ]);

    $result = $this->calculator->calculate($this->store, $this->state->id, $this->city->id);

    expect($result['cost'])->toBe(700.0);
    expect($result['method'])->toBe('rate');
});

test('free_above threshold triggers free shipping', function () {
    $provider = ShippingProvider::create([
        'store_id' => $this->store->id,
        'name' => 'Test',
        'code' => 'test',
        'credentials' => [],
        'is_active' => true,
    ]);

    ShippingRate::create([
        'store_id' => $this->store->id,
        'shipping_provider_id' => $provider->id,
        'state_id' => $this->state->id,
        'cost' => 800,
        'free_above' => 5000,
        'is_active' => true,
    ]);

    $result = $this->calculator->calculate($this->store, $this->state->id, null, 5000);

    expect($result['is_free'])->toBeTrue();
    expect($result['method'])->toBe('free');
});

test('below free_above threshold charges rate', function () {
    $provider = ShippingProvider::create([
        'store_id' => $this->store->id,
        'name' => 'Test',
        'code' => 'test',
        'credentials' => [],
        'is_active' => true,
    ]);

    ShippingRate::create([
        'store_id' => $this->store->id,
        'shipping_provider_id' => $provider->id,
        'state_id' => $this->state->id,
        'cost' => 800,
        'free_above' => 5000,
        'is_active' => true,
    ]);

    $result = $this->calculator->calculate($this->store, $this->state->id, null, 3000);

    expect($result['cost'])->toBe(800.0);
    expect($result['is_free'])->toBeFalse();
});

test('unavailable state returns available false', function () {
    $unavailableState = State::create([
        'country_id' => $this->country->id,
        'state_code' => '99',
        'name' => 'Remote Area',
        'is_active' => true,
        'is_cod_available' => false,
    ]);

    $result = $this->calculator->calculate($this->store, $unavailableState->id);

    expect($result['available'])->toBeFalse();
    expect($result['method'])->toBe('unavailable');
});

test('inactive rates are ignored', function () {
    $provider = ShippingProvider::create([
        'store_id' => $this->store->id,
        'name' => 'Test',
        'code' => 'test',
        'credentials' => [],
        'is_active' => true,
    ]);

    ShippingRate::create([
        'store_id' => $this->store->id,
        'shipping_provider_id' => $provider->id,
        'state_id' => $this->state->id,
        'cost' => 500,
        'is_active' => false,
    ]);

    $result = $this->calculator->calculate($this->store, $this->state->id);

    expect($result['method'])->toBe('free');
});

test('inactive provider flat rate ignored', function () {
    ShippingProvider::create([
        'store_id' => $this->store->id,
        'name' => 'Test',
        'code' => 'test',
        'credentials' => [],
        'is_active' => false,
        'is_default' => true,
        'flat_rate' => 500,
    ]);

    $result = $this->calculator->calculate($this->store);

    expect($result['method'])->toBe('free');
});

// ————— Announced company rates (delivery_rates) —————

function calculatorProvider(Store $store, string $name = 'Yalidine', bool $default = true): ShippingProvider
{
    return ShippingProvider::create([
        'store_id' => $store->id,
        'name' => $name,
        'code' => Str::slug($name).'-'.uniqid(),
        'credentials' => [],
        'is_active' => true,
        'is_default' => $default,
    ]);
}

function calculatorProduct(Store $store, string $name, float $price = 900): Product
{
    $product = Product::create([
        'store_id' => $store->id,
        'name' => $name,
        'slug' => Str::slug($name).'-'.uniqid(),
        'sku' => 'calc-'.uniqid(),
        'price' => $price,
        'is_active' => true,
    ]);

    \App\Models\Products\ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Default',
        'sku' => 'calc-v-'.uniqid(),
        'price' => $price,
        'stock' => 10,
    ]);

    return $product;
}

test('announced company rate takes precedence over legacy rate', function () {
    $provider = calculatorProvider($this->store);

    DeliveryRate::create([
        'store_id' => $this->store->id,
        'shipping_provider_id' => $provider->id,
        'state_id' => $this->state->id,
        'home_cost' => 900,
        'office_cost' => 500,
        'is_active' => true,
    ]);

    ShippingRate::create([
        'store_id' => $this->store->id,
        'shipping_provider_id' => $provider->id,
        'state_id' => $this->state->id,
        'cost' => 400,
        'is_active' => true,
    ]);

    $result = $this->calculator->calculate($this->store, $this->state->id);

    expect($result['cost'])->toBe(900.0)
        ->and($result['method'])->toBe('rate')
        ->and($result['provider_name'])->toBe('Yalidine');
});

test('storefront resolves to the default provider when several have rates', function () {
    calculatorProvider($this->store, 'Default'); // is_default = true
    $other = calculatorProvider($this->store, 'Other', false);

    DeliveryRate::create([
        'store_id' => $this->store->id,
        'shipping_provider_id' => $other->id,
        'state_id' => $this->state->id,
        'home_cost' => 1100,
        'is_active' => true,
    ]);

    DeliveryRate::create([
        'store_id' => $this->store->id,
        'shipping_provider_id' => ShippingProvider::where('store_id', $this->store->id)->where('name', 'Default')->first()->id,
        'state_id' => $this->state->id,
        'home_cost' => 900,
        'is_active' => true,
    ]);

    $result = $this->calculator->calculate($this->store, $this->state->id);

    expect($result['provider_name'])->toBe('Default')
        ->and($result['cost'])->toBe(900.0);
});

test('announced per-municipality rate overrides the state home cost', function () {
    $provider = calculatorProvider($this->store);

    DeliveryRate::create([
        'store_id' => $this->store->id,
        'shipping_provider_id' => $provider->id,
        'state_id' => $this->state->id,
        'home_cost' => 900,
        'is_active' => true,
    ]);

    DeliveryRateCity::create([
        'store_id' => $this->store->id,
        'shipping_provider_id' => $provider->id,
        'state_id' => $this->state->id,
        'city_id' => $this->city->id,
        'home_cost' => 450,
        'is_active' => true,
    ]);

    $result = $this->calculator->calculate($this->store, $this->state->id, $this->city->id);

    expect($result['cost'])->toBe(450.0);
});

test('announced free_above threshold triggers free shipping', function () {
    $provider = calculatorProvider($this->store);

    DeliveryRate::create([
        'store_id' => $this->store->id,
        'shipping_provider_id' => $provider->id,
        'state_id' => $this->state->id,
        'home_cost' => 900,
        'free_above' => 5000,
        'is_active' => true,
    ]);

    $result = $this->calculator->calculate($this->store, $this->state->id, null, 6000);

    expect($result['is_free'])->toBeTrue()
        ->and($result['cost'])->toBe(0);

    $result = $this->calculator->calculate($this->store, $this->state->id, null, 3000);

    expect($result['is_free'])->toBeFalse()
        ->and($result['cost'])->toBe(900.0);
});

// ————— Price lists (delivery_price_lists) —————

test('price list price applies when the whole cart is covered by one list', function () {
    $p1 = calculatorProduct($this->store, 'Alpha');
    $p2 = calculatorProduct($this->store, 'Beta');

    $list = DeliveryPriceList::create([
        'store_id' => $this->store->id,
        'name' => 'Fast',
        'is_active' => true,
    ]);
    $list->products()->attach([$p1->id, $p2->id]);

    DeliveryRateListState::create([
        'delivery_price_list_id' => $list->id,
        'state_id' => $this->state->id,
        'home_cost' => 650,
        'office_cost' => 350,
    ]);

    $result = $this->calculator->calculate($this->store, $this->state->id, null, 0, [$p1->id, $p2->id]);

    expect($result['cost'])->toBe(650.0)
        ->and($result['method'])->toBe('rate')
        ->and($result['provider_name'])->toBe('Fast');
});

test('price list municipality override beats the list state price', function () {
    $p = calculatorProduct($this->store, 'Gamma');

    $list = DeliveryPriceList::create([
        'store_id' => $this->store->id,
        'name' => 'Fast',
        'is_active' => true,
    ]);
    $list->products()->attach($p->id);

    DeliveryRateListState::create([
        'delivery_price_list_id' => $list->id,
        'state_id' => $this->state->id,
        'home_cost' => 650,
        'office_cost' => 350,
    ]);

    DeliveryRateListCity::create([
        'delivery_price_list_id' => $list->id,
        'state_id' => $this->state->id,
        'city_id' => $this->city->id,
        'home_cost' => 400,
    ]);

    $result = $this->calculator->calculate($this->store, $this->state->id, $this->city->id, 0, [$p->id]);

    expect($result['cost'])->toBe(400.0);
});

test('mixed cart falls back to the announced company rate', function () {
    $provider = calculatorProvider($this->store);

    DeliveryRate::create([
        'store_id' => $this->store->id,
        'shipping_provider_id' => $provider->id,
        'state_id' => $this->state->id,
        'home_cost' => 900,
        'is_active' => true,
    ]);

    $inList = calculatorProduct($this->store, 'InList');
    $outside = calculatorProduct($this->store, 'Outside');

    $list = DeliveryPriceList::create([
        'store_id' => $this->store->id,
        'name' => 'Fast',
        'is_active' => true,
    ]);
    $list->products()->attach($inList->id);

    DeliveryRateListState::create([
        'delivery_price_list_id' => $list->id,
        'state_id' => $this->state->id,
        'home_cost' => 650,
        'office_cost' => 350,
    ]);

    $result = $this->calculator->calculate($this->store, $this->state->id, null, 0, [$inList->id, $outside->id]);

    expect($result['provider_name'])->toBe('Yalidine')
        ->and($result['cost'])->toBe(900.0);
});

test('cart covered by two lists falls back to the announced company rate', function () {
    $provider = calculatorProvider($this->store);

    DeliveryRate::create([
        'store_id' => $this->store->id,
        'shipping_provider_id' => $provider->id,
        'state_id' => $this->state->id,
        'home_cost' => 900,
        'is_active' => true,
    ]);

    $p = calculatorProduct($this->store, 'Shared');

    foreach (['ListA', 'ListB'] as $name) {
        $list = DeliveryPriceList::create([
            'store_id' => $this->store->id,
            'name' => $name,
            'is_active' => true,
        ]);
        $list->products()->attach($p->id);

        DeliveryRateListState::create([
            'delivery_price_list_id' => $list->id,
            'state_id' => $this->state->id,
            'home_cost' => $name === 'ListA' ? 650 : 500,
        ]);
    }

    $result = $this->calculator->calculate($this->store, $this->state->id, null, 0, [$p->id]);

    expect($result['provider_name'])->toBe('Yalidine')
        ->and($result['cost'])->toBe(900.0);
});

test('inactive price lists are ignored', function () {
    $provider = calculatorProvider($this->store);

    DeliveryRate::create([
        'store_id' => $this->store->id,
        'shipping_provider_id' => $provider->id,
        'state_id' => $this->state->id,
        'home_cost' => 900,
        'is_active' => true,
    ]);

    $p = calculatorProduct($this->store, 'Inactive');

    $list = DeliveryPriceList::create([
        'store_id' => $this->store->id,
        'name' => 'Old',
        'is_active' => false,
    ]);
    $list->products()->attach($p->id);

    DeliveryRateListState::create([
        'delivery_price_list_id' => $list->id,
        'state_id' => $this->state->id,
        'home_cost' => 650,
    ]);

    $result = $this->calculator->calculate($this->store, $this->state->id, null, 0, [$p->id]);

    expect($result['provider_name'])->toBe('Yalidine')
        ->and($result['cost'])->toBe(900.0);
});
