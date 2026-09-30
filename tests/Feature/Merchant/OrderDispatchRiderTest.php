<?php

use App\Domains\Shipping\Models\DeliveryRider;
use App\Domains\Shipping\Models\ShippingProvider;
use App\Enums\Store\OrderTrackingStatus;
use App\Enums\Store\StorePermissionEnum;
use App\Enums\Store\StoreRoleEnum;
use App\Models\Customer;
use App\Models\Orders\Order;
use App\Models\Orders\OrderTracking;
use App\Models\Status;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Models\User;
use App\Support\StoreRoles;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\StoreRolesAndPermissionsSeeder;
use Database\Seeders\SystemStatusesSeeder;
use Illuminate\Support\Str;
use Livewire\Volt\Volt;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(StoreRolesAndPermissionsSeeder::class);
    $this->seed(SystemStatusesSeeder::class);
});

/**
 * ROLES_PERMISSIONS.md §6.5 splits `order.dispatch.rider` out of `order.assign`
 * at exactly two positions, and the split is a UNION, never a replacement:
 *
 *   1. `TrackingRiderFormConcern::assignRider()`  — the guard  (:29)
 *   2. `tracking/partials/order-drawer.blade.php:132` — the display twin
 *
 * Both must land together: a guard without its twin hides a working button, a
 * twin without its guard shows a button that 403s.
 *
 * The tracking page itself stays gated by Phase 36.9
 * (`tracking/index.blade.php:246` — CRM_ORDER_TRACKING || ORDER_MANAGE) and
 * `openDrawer()` needs ORDER_VIEW, so a rider-only member still needs those two
 * capabilities to reach the drawer at all. Every fixture below therefore grants
 * exactly those two to *reach* the page and then adds the permission under test.
 *
 * `order.assign` and `order.manage` are never granted here: either one would
 * satisfy the union on its own and silently turn every assertion into a pass,
 * which is precisely the regression this phase has to rule out.
 */
const ODR_PAGE_ACCESS = [
    StorePermissionEnum::ORDER_VIEW->value,
    StorePermissionEnum::CRM_ORDER_TRACKING->value,
];

const ODR_RIDER_GRANT = [
    StorePermissionEnum::ORDER_VIEW->value,
    StorePermissionEnum::CRM_ORDER_TRACKING->value,
    StorePermissionEnum::ORDER_DISPATCH_RIDER->value,
];

const ODR_ASSIGN_GRANT = [
    StorePermissionEnum::ORDER_VIEW->value,
    StorePermissionEnum::CRM_ORDER_TRACKING->value,
    StorePermissionEnum::ORDER_ASSIGN->value,
];

function odrOwner(): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate(StoreRoleEnum::OWNER->value, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name'    => 'Rider Dispatch Store',
        'slug'    => 'odr-'.uniqid(),
        'status'  => 'active',
    ]);

    $membership = StoreMembership::create([
        'store_id'   => $store->id,
        'user_id'    => $user->id,
        'invited_by' => $user->id,
        'is_active'  => true,
        'role'       => StoreRoleEnum::OWNER->value,
    ]);
    $membership->syncPermissions(StoreRoles::permissions(StoreRoleEnum::OWNER));

    return [$user, $store, $membership];
}

function odrMember(Store $store, array $permissions, string $name = 'Rep'): array
{
    $user = User::factory()->create(['name' => $name]);

    $membership = StoreMembership::create([
        'store_id'   => $store->id,
        'user_id'    => $user->id,
        'invited_by' => $store->user_id,
        'is_active'  => true,
        'role'       => StoreRoleEnum::STAFF->value,
    ]);
    $membership->syncPermissions($permissions);

    return [$user, $membership];
}

function odrStatus(): Status
{
    return Status::system()->forType('order')->where('key', 'shipped')->firstOrFail();
}

function odrProvider(Store $store, string $name = 'Yalidine'): ShippingProvider
{
    return ShippingProvider::create([
        'store_id'    => $store->id,
        'name'        => $name,
        'code'        => 'sp-'.Str::random(6),
        'credentials' => [],
        'is_active'   => true,
    ]);
}

function odrRider(Store $store, string $name): DeliveryRider
{
    return DeliveryRider::create([
        'store_id'     => $store->id,
        'name'         => $name,
        'phone'        => '0560'.fake()->unique()->numerify('######'),
        'vehicle_type' => DeliveryRider::VEHICLE_CAR,
        'is_active'    => true,
    ]);
}

/**
 * A provider-backed, already-shipped order — the exact state the tracking grid
 * and the drawer's `has_provider` branch care about.
 */
function odrOrder(Store $store, ShippingProvider $provider, string $number): Order
{
    $customer = Customer::create([
        'store_id' => $store->id,
        'name'     => 'ODR Customer '.Str::random(5),
        'phone'    => '0553'.fake()->unique()->numerify('######'),
        'status'   => true,
    ]);

    $order = Order::create([
        'store_id'            => $store->id,
        'customer_id'         => $customer->id,
        'status_id'           => odrStatus()->id,
        'number'              => (new Order(['store_id' => $store->id]))->nextOrderNumber(),
        'total_amount'        => 1900,
        'shipping_cost'       => 0,
        'shipping_provider_id' => $provider->id,
        'delivery_type'       => 'home',
    ]);

    OrderTracking::create([
        'store_id'            => $store->id,
        'order_id'            => $order->id,
        'shipping_provider_id' => $provider->id,
        'tracking_number'     => $number,
        'tracking_status'     => OrderTrackingStatus::SHIPPED->value,
    ]);

    return $order;
}

function odrVolt(User $user, Store $store)
{
    actingAs($user)->withSession(['current_store_id' => $store->id]);
    app(\App\Support\StoreContext::class)->clear();

    return Volt::test('merchant.tracking.index');
}

it('lets a dispatch.rider-only member open and submit the rider form', function () {
    [$owner, $store] = odrOwner();
    [$riderUser, $riderMembership] = odrMember($store, ODR_RIDER_GRANT, 'Rider Dispatcher');
    $provider = odrProvider($store);
    $order = odrOrder($store, $provider, 'ODR-RIDER-1');
    $rider = odrRider($store, 'Field Rider');

    expect($riderMembership->can(StorePermissionEnum::ORDER_DISPATCH_RIDER->value))->toBeTrue()
        ->and($riderMembership->can(StorePermissionEnum::ORDER_ASSIGN->value))->toBeFalse()
        ->and($riderMembership->can(StorePermissionEnum::ORDER_MANAGE->value))->toBeFalse();

    // Riders take over a parcel only while it is not already carrier-backed —
    // assignRider() refuses (warning toast) when a provider is attached.
    $order->update(['shipping_provider_id' => null]);

    odrVolt($riderUser, $store)
        ->set('trackingTab', 'rider')
        ->call('assignRider', (string) $order->id, $rider->id)
        ->assertOk();

    expect($order->refresh()->delivery_rider_id)->toBe($rider->id);

    // ensureRiderTracking() reuses the already-open carrier leg: it clears the
    // provider so bulk sync never probes a local rider number, and keeps the
    // number it already has instead of minting a second leg.
    $legs = OrderTracking::where('order_id', $order->id)->get();

    expect($legs)->toHaveCount(1)
        ->and($legs->first()->tracking_number)->toBe('ODR-RIDER-1')
        ->and($legs->first()->shipping_provider_id)->toBeNull();
});

it('shows the rider-assignment control to a dispatch.rider-only member in the drawer', function () {
    [$owner, $store] = odrOwner();
    [$riderUser] = odrMember($store, ODR_RIDER_GRANT, 'Rider Dispatcher');
    $provider = odrProvider($store);
    $order = odrOrder($store, $provider, 'ODR-RIDER-2');
    $rider = odrRider($store, 'Visible Rider');

    // The control is only rendered when the order is not already carrier-backed.
    $order->update(['shipping_provider_id' => null]);

    odrVolt($riderUser, $store)
        ->call('openDrawer', $order->id)
        ->assertOk()
        ->assertSee('assignRider(', escape: false)
        ->assertSee(__('order_flow.rider_assign'))
        ->assertSee($rider->name);
});

it('still lets an order.assign-only member assign a rider and see the control', function () {
    [$owner, $store] = odrOwner();
    [$assignUser, $assignMembership] = odrMember($store, ODR_ASSIGN_GRANT, 'Team Assigner');
    $provider = odrProvider($store);
    $order = odrOrder($store, $provider, 'ODR-ASSIGN-1');
    $rider = odrRider($store, 'Legacy Rider');

    // The non-regression half of the union: no dispatch.rider, still works.
    expect($assignMembership->can(StorePermissionEnum::ORDER_DISPATCH_RIDER->value))->toBeFalse()
        ->and($assignMembership->can(StorePermissionEnum::ORDER_ASSIGN->value))->toBeTrue();

    $order->update(['shipping_provider_id' => null]);

    odrVolt($assignUser, $store)
        ->call('assignRider', (string) $order->id, $rider->id)
        ->assertOk();

    expect($order->refresh()->delivery_rider_id)->toBe($rider->id);

    odrVolt($assignUser, $store)
        ->call('openDrawer', $order->id)
        ->assertOk()
        ->assertSee('assignRider(', escape: false)
        ->assertSee(__('order_flow.rider_change'))
        ->assertSee($rider->name);
});

it('denies a member holding neither permission and renders no control', function () {
    [$owner, $store] = odrOwner();
    [$plainUser, $plainMembership] = odrMember($store, ODR_PAGE_ACCESS, 'Viewer Only');
    $provider = odrProvider($store);
    $order = odrOrder($store, $provider, 'ODR-NONE-1');
    $rider = odrRider($store, 'Unreachable Rider');

    $order->update(['shipping_provider_id' => null]);

    expect($plainMembership->can(StorePermissionEnum::ORDER_DISPATCH_RIDER->value))->toBeFalse()
        ->and($plainMembership->can(StorePermissionEnum::ORDER_ASSIGN->value))->toBeFalse();

    odrVolt($plainUser, $store)
        ->call('assignRider', (string) $order->id, $rider->id)
        ->assertForbidden();

    expect($order->refresh()->delivery_rider_id)->toBeNull();

    // The twin has to agree with the guard, or the button would be visible
    // while the only thing behind it is a 403.
    odrVolt($plainUser, $store)
        ->call('openDrawer', $order->id)
        ->assertOk()
        ->assertDontSee('assignRider(', escape: false)
        ->assertDontSee(__('order_flow.rider_assign'));
});

it('hides the control when the order already has a shipping provider, even for a dispatcher', function () {
    [$owner, $store] = odrOwner();
    [$riderUser] = odrMember($store, ODR_RIDER_GRANT, 'Rider Dispatcher');
    $provider = odrProvider($store);
    $order = odrOrder($store, $provider, 'ODR-PROV-1');

    expect($order->shipping_provider_id)->toBe($provider->id);

    odrVolt($riderUser, $store)
        ->call('openDrawer', $order->id)
        ->assertOk()
        ->assertDontSee('assignRider(', escape: false);
});

it('does not add dispatch.rider to the manager or staff templates', function () {
    // ROLES_PERMISSIONS.md §6.5 item 7: the new permission is granted
    // individually through the Permission Hub, never by a role template.
    // OWNER/ADMIN are deliberately exempt — they are built from
    // StorePermissionEnum::values() and pick it up with zero StoreRoles.php edits.
    foreach ([StoreRoleEnum::MANAGER, StoreRoleEnum::STAFF] as $role) {
        expect(StoreRoles::permissions($role))
            ->not->toContain(StorePermissionEnum::ORDER_DISPATCH_RIDER->value)
            ->not->toContain(StorePermissionEnum::ORDER_DISPATCH_CARRIER->value);
    }
});

it('takes the new permission from owner and admin through values() alone', function () {
    expect(StoreRoles::permissions(StoreRoleEnum::OWNER))
        ->toContain(StorePermissionEnum::ORDER_DISPATCH_RIDER->value)
        ->and(StoreRoles::permissions(StoreRoleEnum::ADMIN))
        ->toContain(StorePermissionEnum::ORDER_DISPATCH_RIDER->value);
});

it('does not let dispatch.rider satisfy the team-reassignment capability', function () {
    [$owner, $store] = odrOwner();
    [$riderUser, $riderMembership] = odrMember($store, ODR_RIDER_GRANT, 'Rider Dispatcher');

    // helpers.php:192 stays on plain order.assign: handing a parcel to a
    // delivery rider is not moving work between team members, and the union
    // must not have leaked into canReassignOrders().
    actingAs($riderUser)->withSession(['current_store_id' => $store->id]);
    app(\App\Support\StoreContext::class)->clear();

    expect(canReassignOrders($riderUser))->toBeFalse()
        ->and($riderMembership->can(StorePermissionEnum::ORDER_ASSIGN->value))->toBeFalse();
});
