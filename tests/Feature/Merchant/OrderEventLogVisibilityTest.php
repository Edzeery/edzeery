<?php

use App\Enums\Store\StoreRoleEnum;
use App\Models\Customer;
use App\Models\Orders\Order;
use App\Models\Orders\OrderEvent;
use App\Models\Status;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Support\StoreOrderPermissions;
use Livewire\Volt\Volt;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\StoreRolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\SystemStatusesSeeder::class);
});

function evUser(string $storeRole = 'owner'): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate($storeRole, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Ev Store',
        'slug' => 'ev-'.uniqid(),
        'status' => 'active',
    ]);

    $membership = StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'invited_by' => $user->id,
        'is_active' => true,
        'role' => $storeRole,
    ]);

    return [$user, $store, $membership];
}

function evOrder(Store $store, ?string $assignedToMembershipId = null, ?string $eventActorMembershipId = null): Order
{
    $customer = Customer::firstOrCreate(
        ['store_id' => $store->id, 'phone' => '0550'.fake()->unique()->numerify('######')],
        ['name' => 'Ev Customer', 'status' => true],
    );

    $status = Status::system()
        ->forType('order')
        ->where('key', 'confirmed')
        ->firstOrFail();

    $order = Order::create([
        'store_id' => $store->id,
        'customer_id' => $customer->id,
        'status_id' => $status->id,
        'number' => (new Order(['store_id' => $store->id]))->nextOrderNumber(),
        'total_amount' => 400,
        'shipping_cost' => 0,
        'assigned_to_membership_id' => $assignedToMembershipId,
    ]);

    OrderEvent::create([
        'store_id' => $store->id,
        'order_id' => $order->id,
        'actor_membership_id' => $eventActorMembershipId,
        'actor_type' => $eventActorMembershipId ? OrderEvent::ACTOR_MEMBERSHIP : OrderEvent::ACTOR_SYSTEM,
        'event_type' => 'created',
        'message' => 'Seeded audit event',
        'payload' => [],
        'occurred_at' => now(),
    ]);

    return $order;
}

test('owner can always view the order event log', function () {
    [$user, $store, $membership] = evUser(StoreRoleEnum::OWNER->value);
    $order = evOrder($store);

    expect(StoreOrderPermissions::canViewOrderEventLog($order, $membership))->toBeTrue();
});

test('admin can always view the order event log', function () {
    [$user, $store, $membership] = evUser(StoreRoleEnum::ADMIN->value);
    $order = evOrder($store);

    expect(StoreOrderPermissions::canViewOrderEventLog($order, $membership))->toBeTrue();
});

test('manager can view the log only for orders assigned to their membership', function () {
    [$user, $store, $membership] = evUser(StoreRoleEnum::MANAGER->value);

    $assigned = evOrder($store, $membership->id);
    $other = evOrder($store, null);

    expect(StoreOrderPermissions::canViewOrderEventLog($assigned, $membership))->toBeTrue()
        ->and(StoreOrderPermissions::canViewOrderEventLog($other, $membership))->toBeFalse();
});

test('staff can never view the order event log, even when assigned', function () {
    [$user, $store, $membership] = evUser(StoreRoleEnum::STAFF->value);

    $order = evOrder($store, $membership->id);

    expect(StoreOrderPermissions::canViewOrderEventLog($order, $membership))->toBeFalse();
});

test('owner sees the audit timeline when opening order details', function () {
    [$user, $store, $membership] = evUser(StoreRoleEnum::OWNER->value);
    $order = evOrder($store, null, $membership->id);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    $component = Volt::test('merchant.orders.index')
        ->call('openOrderDetails', $order->id)
        ->assertSet('canViewOrderDetailsEvents', true)
        ->assertSee(__('order_flow.order_timeline'))
        ->assertSee($user->name);

    $events = collect($component->get('detailsEvents'));
    expect($events)->not->toBeEmpty()
        ->and($events->pluck('message')->contains('Seeded audit event'))->toBeTrue();
});

test('staff cannot see the audit timeline when opening order details', function () {
    [$user, $store, $membership] = evUser(StoreRoleEnum::STAFF->value);
    $order = evOrder($store, null, $membership->id);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.orders.index')
        ->call('openOrderDetails', $order->id)
        ->assertSet('canViewOrderDetailsEvents', false)
        ->assertSet('detailsEvents', [])
        ->assertDontSee(__('order_flow.order_timeline'));
});

test('tracking drawer hides the audit log from staff', function () {
    [$user, $store, $membership] = evUser(StoreRoleEnum::STAFF->value);
    $order = evOrder($store, null, $membership->id);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.tracking.index')
        ->call('openDrawer', $order->id)
        ->assertSet('canViewDrawerEvents', false)
        ->assertSet('drawerEvents', [])
        ->assertDontSee(__('order_flow.order_timeline'));
});

test('tracking drawer shows the audit log to the owner', function () {
    [$user, $store, $membership] = evUser(StoreRoleEnum::OWNER->value);
    $order = evOrder($store, null, $membership->id);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    $component = Volt::test('merchant.tracking.index')
        ->call('openDrawer', $order->id)
        ->assertSet('canViewDrawerEvents', true)
        ->assertSee(__('order_flow.order_timeline'));

    $events = collect($component->get('drawerEvents'));
    expect($events)->not->toBeEmpty()
        ->and($events->pluck('message')->contains('Seeded audit event'))->toBeTrue();
});