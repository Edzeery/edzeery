<?php

use App\Domains\Shipping\Models\DeliveryRate;
use App\Domains\Shipping\Models\ShippingProvider;
use App\Domains\Shipping\Services\ShippingCostCalculator;
use App\Enums\Store\StoreRoleEnum;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use Illuminate\Support\Str;
use Livewire\Volt\Volt;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\StoreRolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\SystemStatusesSeeder::class);

    [$user, $store] = artEnv();
    $this->user = $user;
    $this->store = $store;

    $country = Country::firstOrCreate(
        ['code' => 'DZ'],
        ['name' => 'Algeria', 'is_active' => true]
    );

    $this->state = State::firstOrCreate(
        ['country_id' => $country->id, 'state_code' => '16'],
        ['name' => 'Alger', 'is_active' => true, 'is_cod_available' => true]
    );

    $this->provider = artProvider($this->store);

    $this->rate = DeliveryRate::create([
        'store_id' => $this->store->id,
        'shipping_provider_id' => $this->provider->id,
        'state_id' => $this->state->id,
        'home_cost' => 900,
        'office_cost' => 350,
        'source' => 'announced',
        'is_active' => true,
    ]);
});

function artEnv(): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate(StoreRoleEnum::OWNER->value, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Toggle Store',
        'slug' => 'toggle-'.uniqid(),
        'status' => 'active',
    ]);

    StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'invited_by' => $user->id,
        'is_active' => true,
        'role' => StoreRoleEnum::OWNER->value,
    ]);

    return [$user, $store];
}

function artProvider(Store $store): ShippingProvider
{
    return ShippingProvider::create([
        'store_id' => $store->id,
        'name' => 'Toggle Carrier',
        'code' => 'toggle-'.uniqid(),
        'credentials' => [],
        'is_active' => true,
        'is_default' => true,
    ]);
}

function artVolt(array $userStore)
{
    [$user, $store] = $userStore;
    actingAs($user)->withSession(['current_store_id' => $store->id]);

    return Volt::test('merchant.delivery.announced-rates');
}

test('the rate row shows a switch and toggling it flips is_active', function () {
    $volt = artVolt([$this->user, $this->store])
        ->call('selectProvider', (string) $this->provider->id)
        ->assertSet('ratesByState.'.$this->state->id.'.is_active', true)
        ->assertSee('role="switch"', false)
        ->call('toggleRateActive', (string) $this->state->id)
        ->assertSet('ratesByState.'.$this->state->id.'.is_active', false);

    expect($this->rate->refresh()->is_active)->toBeFalse();

    $volt->call('toggleRateActive', (string) $this->state->id)
        ->assertSet('ratesByState.'.$this->state->id.'.is_active', true);

    expect($this->rate->refresh()->is_active)->toBeTrue();
});

test('a rate without a persisted row has no switch and the toggle is a no-op', function () {
    $this->rate->delete();

    artVolt([$this->user, $this->store])
        ->call('selectProvider', (string) $this->provider->id)
        ->assertDontSee('role="switch"', false)
        ->call('toggleRateActive', (string) $this->state->id);

    expect(DeliveryRate::where('store_id', $this->store->id)->count())->toBe(0);
});

test('disabling an announced rate removes it from delivery cost calculation', function () {
    $calculator = app(ShippingCostCalculator::class);

    $before = $calculator->calculate(
        $this->store, (string) $this->state->id, null, 0, [], (string) $this->provider->id, ShippingCostCalculator::DELIVERY_STOPDESK,
    );

    expect($before['cost'])->toBe(350.0)
        ->and($before['available'])->toBeTrue();

    $this->rate->update(['is_active' => false]);

    $after = $calculator->calculate(
        $this->store, (string) $this->state->id, null, 0, [], (string) $this->provider->id, ShippingCostCalculator::DELIVERY_STOPDESK,
    );

    expect($after['available'])->toBeFalse();
});

test('the company master switch enables or disables every announced rate at once', function () {
    $country = Country::firstOrCreate(
        ['code' => 'DZ'],
        ['name' => 'Algeria', 'is_active' => true]
    );

    $stateB = State::create([
        'country_id' => $country->id,
        'state_code' => '31',
        'name' => 'Oran',
        'is_active' => true,
        'is_cod_available' => true,
    ]);

    $rateB = DeliveryRate::create([
        'store_id' => $this->store->id,
        'shipping_provider_id' => $this->provider->id,
        'state_id' => $stateB->id,
        'home_cost' => 800,
        'office_cost' => 300,
        'source' => 'announced',
        'is_active' => true,
    ]);

    $volt = artVolt([$this->user, $this->store])
        ->call('selectProvider', (string) $this->provider->id)
        ->assertSee(__('merchant_panel.rates_master_label'))
        ->call('toggleAllRatesActive')
        ->assertSet('ratesByState.'.$this->state->id.'.is_active', false)
        ->assertSet('ratesByState.'.$stateB->id.'.is_active', false);

    expect($this->rate->refresh()->is_active)->toBeFalse()
        ->and($rateB->refresh()->is_active)->toBeFalse();

    $volt->call('toggleAllRatesActive')
        ->assertSet('ratesByState.'.$this->state->id.'.is_active', true)
        ->assertSet('ratesByState.'.$stateB->id.'.is_active', true);

    expect($this->rate->refresh()->is_active)->toBeTrue()
        ->and($rateB->refresh()->is_active)->toBeTrue();
});

test('the company master switch toggles a mixed state into one full state', function () {
    $country = Country::firstOrCreate(
        ['code' => 'DZ'],
        ['name' => 'Algeria', 'is_active' => true]
    );

    $stateB = State::create([
        'country_id' => $country->id,
        'state_code' => '31',
        'name' => 'Oran',
        'is_active' => true,
        'is_cod_available' => true,
    ]);

    $this->rate->update(['is_active' => false]);

    DeliveryRate::create([
        'store_id' => $this->store->id,
        'shipping_provider_id' => $this->provider->id,
        'state_id' => $stateB->id,
        'home_cost' => 800,
        'office_cost' => 300,
        'source' => 'announced',
        'is_active' => true,
    ]);

    artVolt([$this->user, $this->store])
        ->call('selectProvider', (string) $this->provider->id)
        ->call('toggleAllRatesActive')
        ->assertSet('ratesByState.'.$this->state->id.'.is_active', true)
        ->assertSet('ratesByState.'.$stateB->id.'.is_active', true);

    expect(DeliveryRate::where('store_id', $this->store->id)
        ->where('shipping_provider_id', $this->provider->id)
        ->where('is_active', true)->count())->toBe(2);
});

test('the company master switch is hidden and a no-op for a company without rates', function () {
    $this->rate->delete();

    artVolt([$this->user, $this->store])
        ->call('selectProvider', (string) $this->provider->id)
        ->assertDontSee(__('merchant_panel.rates_master_label'))
        ->call('toggleAllRatesActive');

    expect(DeliveryRate::where('store_id', $this->store->id)->count())->toBe(0);
});