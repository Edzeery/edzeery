<?php

use App\Domains\Shipping\Models\ShippingProvider;
use App\Domains\Shipping\Models\ShippingRate;
use App\Enums\Store\StoreRoleEnum;
use App\Models\Customer;
use App\Models\Locations\City;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use App\Models\Orders\Order;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use Livewire\Volt\Volt;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\StoreRolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\SystemStatusesSeeder::class);
});

function inlineOrderUser(string $storeRole = 'owner'): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate($storeRole, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Inline Order Store',
        'slug' => 'inline-order-'.uniqid(),
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

/**
 * Two states + one commune in each, minus a second commune in State A so the
 * city-edit (within revisitation) and cross-wilaya reconciliation are testable.
 */
function inlineOrderGeography(Store $store): array
{
    $country = Country::create([
        'name' => 'Algeria',
        'code' => 'DZ',
        'is_active' => true,
        'is_cod_available' => true,
    ]);

    $stateA = State::create([
        'country_id' => $country->id,
        'state_code' => '16',
        'name' => 'Alger',
        'is_active' => true,
        'is_cod_available' => true,
    ]);

    $stateB = State::create([
        'country_id' => $country->id,
        'state_code' => '31',
        'name' => 'Oran',
        'is_active' => true,
        'is_cod_available' => true,
    ]);

    $cityA = City::create([
        'state_id' => $stateA->id,
        'name' => 'Bab Ezzouar',
        'post_code' => '16028',
        'is_active' => true,
        'is_cod_available' => true,
    ]);

    $cityB = City::create([
        'state_id' => $stateA->id,
        'name' => 'Dar El Beida',
        'post_code' => '16033',
        'is_active' => true,
        'is_cod_available' => true,
    ]);

    $cityC = City::create([
        'state_id' => $stateB->id,
        'name' => 'Bir El Djir',
        'post_code' => '31130',
        'is_active' => true,
        'is_cod_available' => true,
    ]);

    return [$stateA, $stateB, $cityA, $cityB, $cityC];
}

function inlineOrder(Store $store, State $state, ?City $city = null, string $statusKey = 'pending'): Order
{
    $customer = Customer::create([
        'store_id' => $store->id,
        'name' => 'Inline Customer',
        'phone' => '0550000000',
        'status' => true,
    ]);

    $status = \App\Models\Status::system()->forType('order')->where('key', $statusKey)->first();

    return Order::create([
        'store_id' => $store->id,
        'customer_id' => $customer->id,
        'status_id' => $status?->id,
        'number' => (new Order(['store_id' => $store->id]))->nextOrderNumber(),
        'total_amount' => 1000,
        'state_id' => $state->id,
        'city_id' => $city?->id,
        'delivery_type' => 'home',
        'payment_method' => 'cod',
        'shipping_cost' => 0,
    ]);
}

function inlineOrderVolt(\App\Models\User $user, Store $store)
{
    actingAs($user)->withSession(['current_store_id' => $store->id]);

    return Volt::test('merchant.orders.index');
}

test('owner can inline-edit the wilaya, reconciles the commune and recalcs shipping via the calculator', function () {
    [$user, $store] = inlineOrderUser(StoreRoleEnum::OWNER->value);

    ShippingProvider::create([
        'store_id' => $store->id,
        'name' => 'Yalidine',
        'code' => 'yalidine',
        'credentials' => [],
        'is_active' => true,
        'is_default' => true,
        'flat_rate' => 600,
    ]);

    [$stateA, $stateB, $cityA] = inlineOrderGeography($store);
    $order = inlineOrder($store, $stateA, $cityA);

    inlineOrderVolt($user, $store)
        ->call('startOrderWilayaEdit', $order->id)
        ->assertSet('editingField', 'order.wilaya')
        ->assertSet('editingId', $order->id)
        ->call('saveOrderWilaya', $stateB->id)
        ->assertSet('editingField', null)
        ->assertSet('editingError', null);

    $order->refresh();

    expect($order->state_id)->toBe($stateB->id)
        ->and($order->city_id)->toBeNull() // Bab Ezzouar does not belong to Oran
        ->and((float) $order->shipping_cost)->toBe(600.0);

    $activity = Activity::query()->where('event', 'order_wilaya_updated')->latest()->first();

    expect($activity)->not->toBeNull()
        ->and($activity->description)->toBe('Applied order wilaya')
        ->and((string) $activity->subject_id)->toBe((string) $order->id)
        ->and((string) $activity->causer_id)->toBe((string) $user->id)
        ->and($activity->properties->get('record_id'))->toBe($order->id);
});

test('owner can inline-edit the commune and the city rate recalculates the shipping cost', function () {
    [$user, $store] = inlineOrderUser(StoreRoleEnum::OWNER->value);

    $provider = ShippingProvider::create([
        'store_id' => $store->id,
        'name' => 'Test Carrier',
        'code' => 'test',
        'credentials' => [],
        'is_active' => true,
    ]);

    [$stateA, $stateB, $cityA, $cityB] = inlineOrderGeography($store);

    ShippingRate::create([
        'store_id' => $store->id,
        'shipping_provider_id' => $provider->id,
        'state_id' => $stateA->id,
        'city_id' => null,
        'cost' => 800,
        'is_active' => true,
    ]);

    ShippingRate::create([
        'store_id' => $store->id,
        'shipping_provider_id' => $provider->id,
        'state_id' => $stateA->id,
        'city_id' => $cityB->id,
        'cost' => 400,
        'is_active' => true,
    ]);

    $order = inlineOrder($store, $stateA, $cityA);

    inlineOrderVolt($user, $store)
        ->call('startOrderCityEdit', $order->id)
        ->assertSet('editingField', 'order.city')
        ->call('saveOrderCity', $cityB->id)
        ->assertSet('editingField', null)
        ->assertSet('editingError', null);

    $order->refresh();

    expect($order->city_id)->toBe($cityB->id)
        ->and((float) $order->shipping_cost)->toBe(400.0);

    expect(Activity::query()->where('event', 'order_city_updated')->exists())->toBeTrue();
});

test('staff without order.manage permission is forbidden from inline edits', function () {
    [$staff, $store] = inlineOrderUser(StoreRoleEnum::STAFF->value);
    [$stateA, $stateB, $cityA] = inlineOrderGeography($store);

    $order = inlineOrder($store, $stateA, $cityA);

    expect(canStore(\App\Enums\Store\StorePermissionEnum::ORDER_MANAGE->value))->toBeFalse();

    inlineOrderVolt($staff, $store)
        ->call('startOrderWilayaEdit', $order->id)
        ->assertStatus(403);

    inlineOrderVolt($staff, $store)
        ->set('editingField', 'order.wilaya')
        ->set('editingId', $order->id)
        ->set('editingValue', $stateB->id)
        ->call('saveOrderWilaya')
        ->assertStatus(403);

    expect($order->fresh()->state_id)->toBe($stateA->id)
        ->and(Activity::query()->count())->toBe(0);
});

test('geography inline edit is blocked for shipped orders', function () {
    [$user, $store] = inlineOrderUser(StoreRoleEnum::OWNER->value);
    [$stateA, $stateB, $cityA] = inlineOrderGeography($store);

    ShippingProvider::create([
        'store_id' => $store->id,
        'name' => 'Yalidine',
        'code' => 'yalidine',
        'credentials' => [],
        'is_active' => true,
        'is_default' => true,
        'flat_rate' => 600,
    ]);

    $order = inlineOrder($store, $stateA, $cityA, 'shipped');

    inlineOrderVolt($user, $store)
        ->call('startOrderWilayaEdit', $order->id)
        ->set('editingValue', $stateB->id)
        ->call('saveOrderWilaya')
        ->assertSet('editingField', 'order.wilaya')
        ->assertDispatched('swal', type: 'error');

    expect($order->fresh()->state_id)->toBe($stateA->id)
        ->and((float) $order->fresh()->shipping_cost)->toBe(0.0)
        ->and(Activity::query()->count())->toBe(0);
});

test('a commune outside the order wilaya is rejected and nothing persists', function () {
    [$user, $store] = inlineOrderUser(StoreRoleEnum::OWNER->value);
    [$stateA, $stateB, $cityA, $cityB, $cityC] = inlineOrderGeography($store);

    $order = inlineOrder($store, $stateA, $cityA);

    inlineOrderVolt($user, $store)
        ->call('startOrderCityEdit', $order->id)
        ->set('editingValue', $cityC->id) // cityC belongs to stateB, order is in stateA
        ->call('saveOrderCity')
        ->assertSet('editingField', 'order.city')
        ->assertSet('editingError', __('Selected commune does not belong to this wilaya'));

    $order->refresh();

    expect($order->city_id)->toBe($cityA->id)
        ->and((float) $order->shipping_cost)->toBe(0.0);

    expect(Activity::query()->where('event', 'order_city_updated_validation_failed')->exists())->toBeTrue();
});