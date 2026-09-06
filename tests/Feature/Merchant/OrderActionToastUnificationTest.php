<?php

use App\Models\Customer;
use App\Models\Orders\Order;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use Livewire\Volt\Volt;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\StoreRolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\SystemStatusesSeeder::class);
});

function dtuUser(): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate('owner', 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Toast Unif Store',
        'slug' => 'tu-'.uniqid(),
        'status' => 'active',
    ]);

    StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'invited_by' => $user->id,
        'is_active' => true,
        'role' => 'owner',
    ]);

    return [$user, $store];
}

function dtuOrder(Store $store, string $statusKey = 'pending'): Order
{
    $customer = Customer::firstOrCreate(
        ['store_id' => $store->id, 'phone' => '0550123456'],
        ['name' => 'Toast Unif Customer', 'status' => true],
    );

    $status = \App\Models\Status::system()
        ->forType('order')
        ->where('key', $statusKey)
        ->firstOrFail();

    $order = Order::create([
        'store_id' => $store->id,
        'customer_id' => $customer->id,
        'status_id' => $status->id,
        'number' => (new Order(['store_id' => $store->id]))->nextOrderNumber(),
        'total_amount' => 500,
        'shipping_cost' => 0,
    ]);

    return $order;
}

function dtuVolt(array $userStore)
{
    [$user, $store] = $userStore;
    actingAs($user)->withSession(['current_store_id' => $store->id]);

    return Volt::test('merchant.orders.index');
}

// P29.7 — every row/action feedback now goes through the unified swal:toast
// channel (payload: ['icon' => ..., 'title' => ...]), never the intrusive swal
// alert, and never hardcoded English or a raw exception string.

it('bulk assign dispatches a success toast', function () {
    [$user, $store] = dtuUser();
    $order = dtuOrder($store);
    $membership = StoreMembership::where('store_id', $store->id)->firstOrFail();

    dtuVolt([$user, $store])
        ->set('selectedOrders', [$order->id])
        ->call('bulkAssignAgent', $membership->id)
        ->assertDispatched('swal:toast', fn ($name, $params) => ($params[0]['icon'] ?? null) === 'success');
});

it('bulk delete dispatches a success toast', function () {
    [$user, $store] = dtuUser();
    $order = dtuOrder($store);

    dtuVolt([$user, $store])
        ->set('selectedOrders', [$order->id])
        ->call('bulkDelete')
        ->assertDispatched('swal:toast', fn ($name, $params) => ($params[0]['icon'] ?? null) === 'success');

    expect(Order::withTrashed()->find($order->id))->not->toBeNull();
});

it('single delete dispatches a success toast', function () {
    [$user, $store] = dtuUser();
    $order = dtuOrder($store);

    dtuVolt([$user, $store])
        ->call('deleteOrder', $order->id)
        ->assertDispatched('swal:toast', fn ($name, $params) => ($params[0]['icon'] ?? null) === 'success');
});

it('restore from trash dispatches a success toast', function () {
    [$user, $store] = dtuUser();
    $order = dtuOrder($store);
    $order->delete();

    dtuVolt([$user, $store])
        ->call('restoreOrder', $order->id)
        ->assertDispatched('swal:toast', fn ($name, $params) => ($params[0]['icon'] ?? null) === 'success');

    expect(Order::find($order->id))->not->toBeNull();
});

it('transition success dispatches a success toast', function () {
    [$user, $store] = dtuUser();
    $order = dtuOrder($store, 'pending');

    dtuVolt([$user, $store])
        ->call('transitionOrder', $order->id, 'confirmed')
        ->assertDispatched('swal:toast', fn ($name, $params) => ($params[0]['icon'] ?? null) === 'success');

    expect(Order::find($order->id)->status?->key)->toBe('confirmed');
});

it('invalid transition dispatches an error toast without changing the status', function () {
    [$user, $store] = dtuUser();
    $order = dtuOrder($store, 'pending');

    $t = dtuVolt([$user, $store]);
    $t->call('transitionOrder', $order->id, 'confirmed');
    $t->call('transitionOrder', $order->id, 'confirmed')
        ->assertDispatched('swal:toast', fn ($name, $params) => ($params[0]['icon'] ?? null) === 'error');

    expect(Order::find($order->id)->status?->key)->toBe('confirmed');
});

it('reassign dispatches a success toast', function () {
    [$user, $store] = dtuUser();
    $order = dtuOrder($store);
    $membership = StoreMembership::where('store_id', $store->id)->firstOrFail();

    dtuVolt([$user, $store])
        ->set('reassignOrderId', $order->id)
        ->set('reassignMembershipId', $membership->id)
        ->call('submitReassign')
        ->assertDispatched('swal:toast', fn ($name, $params) => ($params[0]['icon'] ?? null) === 'success');
});