<?php

use App\Domains\Shipping\Models\ShippingProvider;
use App\Domains\Shipping\Models\ShippingRate;
use App\Domains\Shipping\Models\StopdeskPoint;
use App\Domains\Shipping\Services\ShippingCostCalculator;
use App\Models\Locations\City;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use App\Models\Stores\Store;
use App\Models\User;

use Database\Seeders\PlansSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\StoreRolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(StoreRolesAndPermissionsSeeder::class);
    $this->seed(PlansSeeder::class);

    $this->user = User::factory()->create();
    $this->store = Store::create([
        'user_id' => $this->user->id,
        'name' => 'Test Store',
        'slug' => 'test-store-' . uniqid(),
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
