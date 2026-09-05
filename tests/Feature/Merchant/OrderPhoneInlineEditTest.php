<?php

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

function phoneUser(string $storeRole = 'owner'): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate($storeRole, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Phone Store',
        'slug' => 'phone-'.uniqid(),
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

function phoneOrder(Store $store, string $phone = '0550000000', ?string $secondary = null): Order
{
    $customer = Customer::create([
        'store_id' => $store->id,
        'name' => 'Phone Customer',
        'phone' => $phone,
        'status' => true,
    ]);

    $status = \App\Models\Status::system()->forType('order')->where('key', 'pending')->first();

    return Order::create([
        'store_id' => $store->id,
        'customer_id' => $customer->id,
        'status_id' => $status?->id,
        'number' => (new Order(['store_id' => $store->id]))->nextOrderNumber(),
        'total_amount' => 1000,
        'delivery_type' => 'home',
        'payment_method' => 'cod',
        'shipping_cost' => 0,
        'phone_secondary' => $secondary,
    ]);
}

function phoneVolt(array $userStore)
{
    [$user, $store] = $userStore;
    actingAs($user)->withSession(['current_store_id' => $store->id]);

    return Volt::test('merchant.orders.index');
}

test('the owner can inline-edit the customer phone and the secondary line', function () {
    [$user, $store] = phoneUser(StoreRoleEnum::OWNER->value);
    $order = phoneOrder($store);

    phoneVolt([$user, $store])
        ->call('startOrderPhoneEdit', $order->id)
        ->assertSet('editingField', 'order.phone')
        ->assertSet('editingId', $order->id)
        ->assertSet('phoneEditPhone', '0550000000')
        ->set('phoneEditPhone', '0550999888')
        ->set('phoneEditSecondary', '0560111222')
        ->call('saveOrderPhone')
        ->assertSet('editingField', null)
        ->assertSet('editingError', null)
        ->assertSet('phoneEditPhone', '');

    $order->refresh();

    expect($order->customer?->phone)->toBe('0550999888')
        ->and($order->phone_secondary)->toBe('0560111222');

    $activity = Activity::query()->where('event', 'order_phone_updated')->latest()->first();
    expect($activity)->not->toBeNull()
        ->and((string) $activity->subject_id)->toBe((string) $order->id);
});

test('clearing the secondary phone stores null', function () {
    [$user, $store] = phoneUser(StoreRoleEnum::OWNER->value);
    $order = phoneOrder($store, secondary: '0560111222');

    phoneVolt([$user, $store])
        ->call('startOrderPhoneEdit', $order->id)
        ->set('phoneEditPhone', '0550000000')
        ->set('phoneEditSecondary', '')
        ->call('saveOrderPhone');

    expect($order->fresh()->phone_secondary)->toBeNull();
});

test('an invalid primary phone is rejected inline without persisting', function () {
    [$user, $store] = phoneUser(StoreRoleEnum::OWNER->value);
    $order = phoneOrder($store);

    phoneVolt([$user, $store])
        ->call('startOrderPhoneEdit', $order->id)
        ->set('phoneEditPhone', '0999')
        ->set('phoneEditSecondary', '')
        ->call('saveOrderPhone')
        ->assertSet('editingField', 'order.phone');

    $order->refresh();

    expect($order->customer?->phone)->toBe('0550000000');
});

test('staff without order.manage permission cannot edit the phone inline', function () {
    [$staff, $store] = phoneUser(StoreRoleEnum::STAFF->value);
    $order = phoneOrder($store);

    phoneVolt([$staff, $store])
        ->call('startOrderPhoneEdit', $order->id)
        ->assertStatus(403);

    phoneVolt([$staff, $store])
        ->set('editingField', 'order.phone')
        ->set('editingId', $order->id)
        ->set('phoneEditPhone', '0550123456')
        ->call('saveOrderPhone')
        ->assertStatus(403);

    expect($order->fresh()->customer?->phone)->toBe('0550000000');
});