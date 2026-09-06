<?php

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

function selectEditUser(string $storeRole = 'owner'): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate($storeRole, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Inline Select Store',
        'slug' => 'inline-select-'.uniqid(),
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

function selectEditGeography(Store $store): array
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

    $city = City::create([
        'state_id' => $state->id,
        'name' => 'Bab Ezzouar',
        'post_code' => '16028',
        'is_active' => true,
        'is_cod_available' => true,
    ]);

    return [$state, $city];
}

function selectEditProvider(Store $store, string $name, bool $default = false): ShippingProvider
{
    return ShippingProvider::create([
        'store_id' => $store->id,
        'name' => $name,
        'code' => strtolower(preg_replace('/[^A-Za-z]/', '', $name)),
        'credentials' => [],
        'is_active' => true,
        'is_default' => $default,
        'flat_rate' => 600,
    ]);
}

function selectEditOffice(Store $store, ?ShippingProvider $provider, string $name): StopdeskPoint
{
    return StopdeskPoint::create([
        'store_id' => $store->id,
        'shipping_provider_id' => $provider?->id,
        'name' => $name,
        'is_active' => true,
    ]);
}

function selectEditOrder(
    Store $store,
    State $state,
    ?City $city = null,
    string $statusKey = 'pending',
    string $deliveryType = 'home',
    ?ShippingProvider $provider = null,
    ?StopdeskPoint $office = null,
): Order {
    $customer = Customer::create([
        'store_id' => $store->id,
        'name' => 'Select Customer',
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
        'delivery_type' => $deliveryType,
        'payment_method' => 'cod',
        'shipping_provider_id' => $provider?->id,
        'stopdesk_point_id' => $office?->id,
        'shipping_cost' => 0,
    ]);
}

function selectEditVolt(\App\Models\User $user, Store $store)
{
    actingAs($user)->withSession(['current_store_id' => $store->id]);

    return Volt::test('merchant.orders.index');
}

test('owner can inline-edit the shipping provider, which recalcs carrier and logs the audit event', function () {
    [$user, $store] = selectEditUser(StoreRoleEnum::OWNER->value);
    $provider = selectEditProvider($store, 'Yalidine', true);
    [$stateA, $cityA] = selectEditGeography($store);

    $order = selectEditOrder($store, $stateA, $cityA);

    selectEditVolt($user, $store)
        ->call('startOrderProviderEdit', $order->id)
        ->assertSet('editingField', 'order.shipping_provider')
        ->assertSet('editingId', $order->id)
        ->call('saveOrderProvider', $provider->id)
        ->assertSet('editingField', null)
        ->assertSet('editingError', null);

    $order->refresh();

    expect($order->shipping_provider_id)->toBe($provider->id);

    $activity = Activity::query()->where('event', 'order_shipping_provider_updated')->latest()->first();

    expect($activity)->not->toBeNull()
        ->and($activity->description)->toBe('Applied order shipping provider')
        ->and((string) $activity->subject_id)->toBe((string) $order->id);
});

test('switching carrier drops a stopdesk office the new carrier does not serve', function () {
    [$user, $store] = selectEditUser(StoreRoleEnum::OWNER->value);
    $providerA = selectEditProvider($store, 'Aramex');
    $providerB = selectEditProvider($store, 'Yalidine');
    $officeA = selectEditOffice($store, $providerA, 'Aramex Zeralda');

    [$stateA] = selectEditGeography($store);

    $order = selectEditOrder($store, $stateA, null, 'pending', 'stopdesk', $providerA, $officeA);

    selectEditVolt($user, $store)
        ->call('startOrderProviderEdit', $order->id)
        ->call('saveOrderProvider', $providerB->id)
        ->assertSet('editingField', null)
        ->assertSet('editingError', null);

    $order->refresh();

    expect($order->shipping_provider_id)->toBe($providerB->id)
        ->and($order->stopdesk_point_id)->toBeNull();
});

test('a shared stopdesk office survives a carrier switch', function () {
    [$user, $store] = selectEditUser(StoreRoleEnum::OWNER->value);
    $providerA = selectEditProvider($store, 'Aramex');
    $providerB = selectEditProvider($store, 'Yalidine');
    $sharedOffice = selectEditOffice($store, null, 'Shared Point');

    [$stateA] = selectEditGeography($store);

    $order = selectEditOrder($store, $stateA, null, 'pending', 'stopdesk', $providerA, $sharedOffice);

    selectEditVolt($user, $store)
        ->call('startOrderProviderEdit', $order->id)
        ->call('saveOrderProvider', $providerB->id)
        ->assertSet('editingField', null);

    $order->refresh();

    expect($order->shipping_provider_id)->toBe($providerB->id)
        ->and($order->stopdesk_point_id)->toBe($sharedOffice->id);
});

test('owner can inline-edit the delivery type and switching to home clears the office', function () {
    [$user, $store] = selectEditUser(StoreRoleEnum::OWNER->value);
    $provider = selectEditProvider($store, 'Yalidine');
    $office = selectEditOffice($store, $provider, 'Point 16');

    [$stateA] = selectEditGeography($store);

    $order = selectEditOrder($store, $stateA, null, 'pending', 'stopdesk', $provider, $office);

    selectEditVolt($user, $store)
        ->call('startOrderDeliveryTypeEdit', $order->id)
        ->assertSet('editingField', 'order.delivery_type')
        ->call('saveOrderDeliveryType', 'home')
        ->assertSet('editingField', null)
        ->assertSet('editingError', null);

    $order->refresh();

    expect($order->delivery_type)->toBe('home')
        ->and($order->stopdesk_point_id)->toBeNull()
        ->and(Activity::query()->where('event', 'order_delivery_type_updated')->exists())->toBeTrue();
});

test('owner can inline-edit the shipment type', function () {
    [$user, $store] = selectEditUser(StoreRoleEnum::OWNER->value);
    [$stateA] = selectEditGeography($store);

    $order = selectEditOrder($store, $stateA);

    selectEditVolt($user, $store)
        ->call('startOrderShipmentTypeEdit', $order->id)
        ->assertSet('editingField', 'order.shipment_type')
        ->call('saveOrderShipmentType', 'exchange')
        ->assertSet('editingField', null)
        ->assertSet('editingError', null);

    $order->refresh();

    expect($order->shipment_type)->toBe('exchange')
        ->and(Activity::query()->where('event', 'order_shipment_type_updated')->exists())->toBeTrue();
});

test('owner can inline-edit the stopdesk point scoped to the order carrier', function () {
    [$user, $store] = selectEditUser(StoreRoleEnum::OWNER->value);
    $provider = selectEditProvider($store, 'Yalidine');
    $office = selectEditOffice($store, $provider, 'Yalidine Dar El Beida');

    [$stateA] = selectEditGeography($store);

    $order = selectEditOrder($store, $stateA, null, 'pending', 'stopdesk', $provider);

    selectEditVolt($user, $store)
        ->call('startOrderStopdeskEdit', $order->id)
        ->assertSet('editingField', 'order.stopdesk_point')
        ->call('saveOrderStopdesk', $office->id)
        ->assertSet('editingField', null)
        ->assertSet('editingError', null);

    $order->refresh();

    expect($order->stopdesk_point_id)->toBe($office->id)
        ->and(Activity::query()->where('event', 'order_stopdesk_point_updated')->exists())->toBeTrue();
});

test('a stopdesk point belonging to another carrier is rejected inline', function () {
    [$user, $store] = selectEditUser(StoreRoleEnum::OWNER->value);
    $providerA = selectEditProvider($store, 'Aramex');
    $providerB = selectEditProvider($store, 'Yalidine');
    $officeB = selectEditOffice($store, $providerB, 'Yalidine Office');

    [$stateA] = selectEditGeography($store);

    $order = selectEditOrder($store, $stateA, null, 'pending', 'stopdesk', $providerA);

    selectEditVolt($user, $store)
        ->call('startOrderStopdeskEdit', $order->id)
        ->set('editingValue', $officeB->id)
        ->call('saveOrderStopdesk')
        ->assertSet('editingField', 'order.stopdesk_point')
        ->assertSet('editingError', __('Selected point does not belong to this company'));

    $order->refresh();

    expect($order->stopdesk_point_id)->toBeNull();

    expect(Activity::query()->where('event', 'order_stopdesk_point_updated_validation_failed')->exists())->toBeTrue();
});

test('owner can inline-assign an agent and unassign again', function () {
    [$user, $store] = selectEditUser(StoreRoleEnum::OWNER->value);
    [$stateA] = selectEditGeography($store);

    $agentUser = roleUser('merchant');
    $agentUser->assignRole(Role::findOrCreate(StoreRoleEnum::STAFF->value, 'merchant'));

    $agentMembership = StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $agentUser->id,
        'invited_by' => $user->id,
        'is_active' => true,
        'role' => StoreRoleEnum::STAFF->value,
    ]);

    $order = selectEditOrder($store, $stateA);

    selectEditVolt($user, $store)
        ->call('startOrderAgentEdit', $order->id)
        ->assertSet('editingField', 'order.assigned_agent')
        ->call('saveOrderAgent', $agentMembership->id)
        ->assertSet('editingField', null)
        ->assertSet('editingError', null);

    $order->refresh();

    expect($order->assigned_to_membership_id)->toBe($agentMembership->id)
        ->and($order->assignment_method)->toBe('manual');

    expect(Activity::query()->where('event', 'order_assigned_agent_updated')->exists())->toBeTrue();

    selectEditVolt($user, $store)
        ->call('startOrderAgentEdit', $order->id)
        ->set('editingValue', '')
        ->call('saveOrderAgent')
        ->assertSet('editingField', null);

    $order->refresh();

    expect($order->assigned_to_membership_id)->toBeNull()
        ->and($order->assignment_method)->toBeNull();
});

test('agent inline reassignment keeps working on shipped orders', function () {
    [$user, $store] = selectEditUser(StoreRoleEnum::OWNER->value);
    [$stateA] = selectEditGeography($store);

    $agentUser = roleUser('merchant');
    $agentUser->assignRole(Role::findOrCreate(StoreRoleEnum::STAFF->value, 'merchant'));

    $agentMembership = StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $agentUser->id,
        'invited_by' => $user->id,
        'is_active' => true,
        'role' => StoreRoleEnum::STAFF->value,
    ]);

    $order = selectEditOrder($store, $stateA, null, 'shipped');

    selectEditVolt($user, $store)
        ->call('startOrderAgentEdit', $order->id)
        ->call('saveOrderAgent', $agentMembership->id)
        ->assertSet('editingField', null);

    $order->refresh();

    expect($order->assigned_to_membership_id)->toBe($agentMembership->id);
});

test('geography-flavoured inline edits stay blocked for shipped orders, unlike assignment', function () {
    [$user, $store] = selectEditUser(StoreRoleEnum::OWNER->value);
    $provider = selectEditProvider($store, 'Yalidine');
    [$stateA] = selectEditGeography($store);

    $order = selectEditOrder($store, $stateA, null, 'shipped');

    selectEditVolt($user, $store)
        ->call('startOrderProviderEdit', $order->id)
        ->set('editingValue', $provider->id)
        ->call('saveOrderProvider')
        ->assertSet('editingField', 'order.shipping_provider');

    $order->refresh();

    expect($order->shipping_provider_id)->toBeNull();
});

test('staff without order.manage permission is forbidden from the inline selects', function () {
    [$staff, $store] = selectEditUser(StoreRoleEnum::STAFF->value);
    $provider = selectEditProvider($store, 'Yalidine');
    [$stateA] = selectEditGeography($store);

    $order = selectEditOrder($store, $stateA);

    selectEditVolt($staff, $store)
        ->call('startOrderProviderEdit', $order->id)
        ->assertDispatched('swal:toast', fn ($name, $params) => ($params[0]['icon'] ?? null) === 'error'
            && ($params[0]['title'] ?? null) === __('messages.permission_denied'));

    selectEditVolt($staff, $store)
        ->call('startOrderAgentEdit', $order->id)
        ->assertDispatched('swal:toast', fn ($name, $params) => ($params[0]['icon'] ?? null) === 'error'
            && ($params[0]['title'] ?? null) === __('messages.permission_denied'));

    expect(Activity::query()->count())->toBe(0)
        ->and($order->fresh()->shipping_provider_id)->toBeNull();
});