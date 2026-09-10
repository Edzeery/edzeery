<?php

use App\Domains\Shipping\Models\Carrier;
use App\Domains\Shipping\Models\CarrierPlatform;
use App\Domains\Shipping\Models\DeliveryRate;
use App\Domains\Shipping\Models\DeliveryRateCity;
use App\Domains\Shipping\Models\ShippingProvider;
use App\Domains\Shipping\Models\StopdeskPoint;
use App\Enums\Store\StoreRoleEnum;
use App\Models\Customer;
use App\Models\Locations\City;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use App\Models\Orders\Order;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use Illuminate\Support\Facades\Http;
use Livewire\Volt\Volt;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\StoreRolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\SystemStatusesSeeder::class);
});

function cityScopeUser(string $storeRole = 'owner'): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate($storeRole, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'City Scope Store',
        'slug' => 'city-scope-'.uniqid(),
        'status' => 'active',
    ]);

    StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'invited_by' => $user->id,
        'is_active' => true,
        'role' => $storeRole,
    ]);

    return [$user, $store];
}

function cityScopeGeography(): array
{
    $country = Country::create([
        'name' => 'Algeria',
        'code' => 'DZ',
        'is_active' => true,
        'is_cod_available' => true,
    ]);

    $state = State::create([
        'country_id' => $country->id,
        'state_code' => '16',
        'name' => 'Alger',
        'is_active' => true,
        'is_cod_available' => true,
    ]);

    $sister = State::create([
        'country_id' => $country->id,
        'state_code' => '31',
        'name' => 'Oran',
        'is_active' => true,
        'is_cod_available' => true,
    ]);

    return [$country, $state, $sister];
}

function cityScopeCity(State $state, string $name): City
{
    return City::create([
        'state_id' => $state->id,
        'name' => $name,
        'post_code' => '16000',
        'is_active' => true,
        'is_cod_available' => true,
    ]);
}

function cityScopeProvider(Store $store, ?Carrier $carrier = null): ShippingProvider
{
    return ShippingProvider::create([
        'store_id' => $store->id,
        'name' => $carrier ? $carrier->name : 'Manual Carrier',
        'code' => $carrier?->code ?? 'manual',
        'carrier_id' => $carrier?->id,
        'credentials' => $carrier ? ['api_token' => 'test-token', 'guid' => 'test-guid'] : [],
        'is_active' => true,
        'is_default' => true,
    ]);
}

function cityScopePoint(Store $store, ShippingProvider $provider, State $state, City $city, string $name): StopdeskPoint
{
    return StopdeskPoint::create([
        'store_id' => $store->id,
        'shipping_provider_id' => $provider->id,
        'state_id' => $state->id,
        'city_id' => $city->id,
        'name' => $name,
        'address' => '',
        'is_active' => true,
    ]);
}

function cityScopeVolt(array $userStore)
{
    [$user, $store] = $userStore;
    actingAs($user)->withSession(['current_store_id' => $store->id]);

    return Volt::test('merchant.orders.index');
}

function cityScopeSortedIds(array $rows): array
{
    $ids = collect($rows)->pluck('id')->map(fn ($id) => (string) $id)->all();
    sort($ids);

    return $ids;
}

test('office delivery scopes the commune list to the selected carrier points in the chosen wilaya', function () {
    [$user, $store] = cityScopeUser(StoreRoleEnum::OWNER->value);
    [, $state] = cityScopeGeography();

    $cityCoveredA = cityScopeCity($state, 'Bab Ezzouar');
    $cityCoveredB = cityScopeCity($state, 'Cheraga');
    $cityUncovered = cityScopeCity($state, 'Dar El Beida');

    $provider = cityScopeProvider($store);
    cityScopePoint($store, $provider, $state, $cityCoveredA, 'Point A');
    cityScopePoint($store, $provider, $state, $cityCoveredB, 'Point B');

    $volt = cityScopeVolt([$user, $store])
        ->set('form.delivery_type', 'stopdesk')
        ->set('form.shipping_provider_id', $provider->id)
        ->call('loadCities', (string) $state->id)
        ->call('loadFormCitiesLazy', 'stopdesk|' . $provider->id . '|' . $state->id);

    $coveredIds = cityScopeSortedIds($volt->get('formCities'));

    $expected = array_map(strval(...), [$cityCoveredA->id, $cityCoveredB->id]);
    sort($expected);

    expect($coveredIds)->toBe($expected)
        ->and($volt->get('formCoverageHint'))->toBe('');
});

test('a carrier without points in the chosen wilaya shows no communes and never falls back', function () {
    [$user, $store] = cityScopeUser(StoreRoleEnum::OWNER->value);
    [$country, $state, $sister] = cityScopeGeography();

    $cityAlger = cityScopeCity($state, 'Bab Ezzouar');
    $cityOran = cityScopeCity($sister, 'Oran City');

    $provider = cityScopeProvider($store);
    cityScopePoint($store, $provider, $state, $cityAlger, 'Algiers Point');

    $volt = cityScopeVolt([$user, $store])
        ->set('form.delivery_type', 'stopdesk')
        ->set('form.shipping_provider_id', $provider->id)
        ->call('loadCities', (string) $sister->id);

    expect($volt->get('allCities'))->toBe([])
        ->and($volt->get('formCoverageHint'))->toBe('no_company_coverage');
});

test('home delivery keeps all communes of the wilaya regardless of the carrier', function () {
    [$user, $store] = cityScopeUser(StoreRoleEnum::OWNER->value);
    [, $state] = cityScopeGeography();

    $cityA = cityScopeCity($state, 'Bab Ezzouar');
    $cityB = cityScopeCity($state, 'Cheraga');
    $cityC = cityScopeCity($state, 'Dar El Beida');

    $provider = cityScopeProvider($store);
    cityScopePoint($store, $provider, $state, $cityA, 'Point A');

    $volt = cityScopeVolt([$user, $store])
        ->set('form.delivery_type', 'home')
        ->set('form.shipping_provider_id', $provider->id)
        ->call('loadCities', (string) $state->id)
        ->call('loadFormCitiesLazy', 'home|' . $provider->id . '|' . $state->id);

    $expected = array_map(strval(...), [$cityA->id, $cityB->id, $cityC->id]);
    sort($expected);

    expect(cityScopeSortedIds($volt->get('formCities')))->toBe($expected);
});

test('a carrier-backed provider still scopes communes and survives a failed office sync', function () {
    [$user, $store] = cityScopeUser(StoreRoleEnum::OWNER->value);
    [, $state] = cityScopeGeography();

    $cityCovered = cityScopeCity($state, 'Bab Ezzouar');
    $cityUncovered = cityScopeCity($state, 'Dar El Beida');

    $platform = CarrierPlatform::create(['name' => 'Noest', 'slug' => 'noest-'.uniqid(), 'is_active' => true]);
    $carrier = Carrier::create([
        'platform_id' => $platform->id,
        'name' => 'NOEST',
        'code' => 'noest',
        'credential_fields' => [],
        'is_active' => true,
    ]);

    Http::fake();

    $provider = cityScopeProvider($store, $carrier);
    cityScopePoint($store, $provider, $state, $cityCovered, 'Synced Point');

    $volt = cityScopeVolt([$user, $store])
        ->set('form.delivery_type', 'stopdesk')
        ->set('form.shipping_provider_id', $provider->id)
        ->call('loadCities', (string) $state->id)
        ->call('loadFormCitiesLazy', 'stopdesk|' . $provider->id . '|' . $state->id);

    $expected = array_map(strval(...), [$cityCovered->id]);
    sort($expected);

    expect(cityScopeSortedIds($volt->get('formCities')))->toBe($expected)
        ->and($volt->get('formCoverageHint'))->toBe('');
});

test('the inline city editor is scoped to the order shipping company', function () {
    [$user, $store] = cityScopeUser(StoreRoleEnum::OWNER->value);
    [, $state] = cityScopeGeography();

    $cityCovered = cityScopeCity($state, 'Bab Ezzouar');
    $cityUncovered = cityScopeCity($state, 'Dar El Beida');

    $provider = cityScopeProvider($store);
    $point = cityScopePoint($store, $provider, $state, $cityCovered, 'Point A');

    $customer = Customer::create([
        'store_id' => $store->id,
        'name' => 'City Scope Customer',
        'phone' => '0551234567',
        'status' => true,
    ]);

    $status = \App\Models\Status::system()->forType('order')->where('key', 'pending')->first();

    $order = Order::create([
        'store_id' => $store->id,
        'customer_id' => $customer->id,
        'status_id' => $status?->id,
        'number' => (new Order(['store_id' => $store->id]))->nextOrderNumber(),
        'total_amount' => 500,
        'state_id' => $state->id,
        'city_id' => $cityCovered->id,
        'delivery_type' => 'stopdesk',
        'shipping_provider_id' => $provider->id,
        'stopdesk_point_id' => $point->id,
        'payment_method' => 'cod',
        'shipping_cost' => 0,
    ]);

    $volt = cityScopeVolt([$user, $store])
        ->call('startOrderCityEdit', $order->id);

    $expected = array_map(strval(...), [$cityCovered->id]);
    sort($expected);

    expect(cityScopeSortedIds($volt->get('editCityOptions')))->toBe($expected);
});

test('home delivery scopes communes to announced per-commune rates when no state-level rate exists', function () {
    [$user, $store] = cityScopeUser(StoreRoleEnum::OWNER->value);
    [, $state] = cityScopeGeography();

    $cityPriced = cityScopeCity($state, 'Bab Ezzouar');
    $cityFree = cityScopeCity($state, 'Cheraga');

    $provider = cityScopeProvider($store);

    DeliveryRateCity::create([
        'store_id' => $store->id,
        'shipping_provider_id' => $provider->id,
        'state_id' => $state->id,
        'city_id' => $cityPriced->id,
        'home_cost' => 400,
        'is_active' => true,
    ]);

    $volt = cityScopeVolt([$user, $store])
        ->set('form.delivery_type', 'home')
        ->set('form.shipping_provider_id', $provider->id)
        ->call('loadCities', (string) $state->id)
        ->call('loadFormCitiesLazy', 'home|' . $provider->id . '|' . $state->id);

    expect(cityScopeSortedIds($volt->get('formCities')))->toBe([(string) $cityPriced->id])
        ->and($volt->get('formCoverageHint'))->toBe('');
});

test('home delivery with a state-level announced rate keeps all communes', function () {
    [$user, $store] = cityScopeUser(StoreRoleEnum::OWNER->value);
    [, $state] = cityScopeGeography();

    $cityA = cityScopeCity($state, 'Bab Ezzouar');
    $cityB = cityScopeCity($state, 'Cheraga');
    $cityC = cityScopeCity($state, 'Dar El Beida');

    $provider = cityScopeProvider($store);

    DeliveryRate::create([
        'store_id' => $store->id,
        'shipping_provider_id' => $provider->id,
        'state_id' => $state->id,
        'home_cost' => 300,
        'is_active' => true,
    ]);

    $volt = cityScopeVolt([$user, $store])
        ->set('form.delivery_type', 'home')
        ->set('form.shipping_provider_id', $provider->id)
        ->call('loadCities', (string) $state->id)
        ->call('loadFormCitiesLazy', 'home|' . $provider->id . '|' . $state->id);

    $expected = array_map(strval(...), [$cityA->id, $cityB->id, $cityC->id]);
    sort($expected);

    expect(cityScopeSortedIds($volt->get('formCities')))->toBe($expected)
        ->and($volt->get('formCoverageHint'))->toBe('');
});

test('home delivery scopes the wilaya list to the states with home prices', function () {
    [$user, $store] = cityScopeUser(StoreRoleEnum::OWNER->value);
    [, $stateAlger, $sisterOran] = cityScopeGeography();

    cityScopeCity($stateAlger, 'Bab Ezzouar');
    cityScopeCity($sisterOran, 'Oran City');

    $provider = cityScopeProvider($store);

    DeliveryRate::create([
        'store_id' => $store->id,
        'shipping_provider_id' => $provider->id,
        'state_id' => $stateAlger->id,
        'home_cost' => 300,
        'is_active' => true,
    ]);

    $volt = cityScopeVolt([$user, $store])
        ->set('form.delivery_type', 'home')
        ->set('form.shipping_provider_id', $provider->id)
        ->call('loadFormScope');

    $scoped = collect($volt->get('formAvailableStates'))->pluck('id')->map(fn ($id) => (string) $id)->all();

    expect($scoped)->toBe([(string) $stateAlger->id]);
});