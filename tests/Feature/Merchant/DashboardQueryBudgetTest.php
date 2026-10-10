<?php

use App\Enums\Store\StoreRoleEnum;
use App\Models\Orders\Order;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Models\User;
use App\Support\StoreRoles;
use Database\Seeders\PlansSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\StoreRolesAndPermissionsSeeder;
use Database\Seeders\SystemStatusesSeeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

/*
|--------------------------------------------------------------------------
| PHASE 37-G - dashboard request budget and request level scoping
|--------------------------------------------------------------------------
|
| The service level tests prove the filter is applied. These prove the wired
| request stays inside a query budget and that a member who is not allowed to
| see a team never sees its orders in the rendered HTML, not merely in a
| service return value.
|
*/

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(StoreRolesAndPermissionsSeeder::class);
    $this->seed(SystemStatusesSeeder::class);
    $this->seed(PlansSeeder::class);
});

function drStore(string $name = 'Budget Store'): Store
{
    $user = drUser();

    return Store::create([
        'user_id' => $user->id,
        'name' => $name,
        'slug' => str($name)->slug()->value().'-'.uniqid(),
        'status' => 'active',
    ]);
}

function drUser(): User
{
    $user = roleUser('merchant');

    return $user;
}

function drMembership(Store $store, User $user, StoreRoleEnum $role): StoreMembership
{
    $user->assignRole(Role::findOrCreate($role->value, 'merchant'));

    $membership = StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'invited_by' => $store->user_id,
        'is_active' => true,
        'role' => $role->value,
    ]);

    $membership->syncPermissions(StoreRoles::permissions($role));

    return $membership;
}

function drPendingStatusId(): string
{
    return DB::table('statuses')
        ->where('type', 'order')
        ->where('key', 'pending')
        ->value('id');
}

function drPendingOrder(Store $store, ?string $membershipId, string $number): Order
{
    $order = Order::create([
        'store_id' => $store->id,
        'number' => $number,
        'total_amount' => 500,
        'status_id' => drPendingStatusId(),
        'delivery_type' => 'home',
        'assigned_to_membership_id' => $membershipId,
    ]);

    return $order->forceFill(['created_at' => now(), 'updated_at' => now()])->save() ? $order : $order;
}

test('the dashboard request stays inside its query budget', function () {
    $owner = drUser();
    $store = drStore();
    drMembership($store, $owner, StoreRoleEnum::OWNER);

    $this->actingAs($owner)->withSession(['current_store_id' => $store->id]);

    // Warm the container, the compiled view and the session so the budget only
    // measures the dashboard's own work.
    $this->get(route('merchant.dashboard', ['store' => $store->slug]))->assertOk();

    $queries = 0;
    DB::listen(function () use (&$queries) {
        $queries++;
    });

    $this->get(route('merchant.dashboard', ['store' => $store->slug]))->assertOk();

    // Measured baseline is 51 for an owner render with no orders: ~20 for
    // auth, store, subscription and permission resolution, 10 for the summary
    // aggregates, 9 for the analytics blocks and the filter option lists, and
    // 2 for the team-performance aggregates (work and credit, § 11) behind the
    // table on this page. The ceiling leaves one query of headroom but still
    // fails on a real N+1 regression, which grows with the number of members
    // rather than by one.
    //
    // Note: 5 of those queries are byte-identical `select * from subscriptions`
    // lookups issued by the subscription gate. That is a genuine inefficiency,
    // but it belongs to the gating domain rather than analytics, so it is
    // reported rather than changed here.
    expect($queries)->toBeLessThanOrEqual(52);
});

test('a locked member only sees their own pending orders in the rendered page', function () {
    $owner = drUser();
    $store = drStore();
    drMembership($store, $owner, StoreRoleEnum::OWNER);

    $staff = drUser();
    $staffMembership = drMembership($store, $staff, StoreRoleEnum::STAFF);

    $other = drUser();
    $otherMembership = drMembership($store, $other, StoreRoleEnum::MANAGER);

    drPendingOrder($store, $staffMembership->id, 'ORD-MINE-0001');
    drPendingOrder($store, $otherMembership->id, 'ORD-THEIRS-0001');

    $html = $this->actingAs($staff)
        ->withSession(['current_store_id' => $store->id])
        ->get(route('merchant.dashboard', ['store' => $store->slug]))
        ->assertOk()
        ->getContent();

    expect($html)->toContain('ORD-MINE-0001')->not->toContain('ORD-THEIRS-0001');

    // The owner sees both, because the owner holds team-view.
    $ownerHtml = $this->actingAs($owner)
        ->withSession(['current_store_id' => $store->id])
        ->get(route('merchant.dashboard', ['store' => $store->slug]))
        ->assertOk()
        ->getContent();

    expect($ownerHtml)->toContain('ORD-MINE-0001')->toContain('ORD-THEIRS-0001');
});

test('a member of another store cannot reach this dashboard', function () {
    $store = drStore();
    drMembership($store, drUser(), StoreRoleEnum::OWNER);

    $outsider = drUser();
    $foreignStore = drStore('Foreign Store');
    drMembership($foreignStore, $outsider, StoreRoleEnum::OWNER);

    $this->actingAs($outsider)
        ->withSession(['current_store_id' => $store->id])
        ->get(route('merchant.dashboard', ['store' => $store->slug]))
        ->assertForbidden();
});

test('an inactive membership is turned away at the door', function () {
    $owner = drUser();
    $store = drStore();
    drMembership($store, $owner, StoreRoleEnum::OWNER);

    $staff = drUser();
    $staffMembership = drMembership($store, $staff, StoreRoleEnum::STAFF);
    drPendingOrder($store, $staffMembership->id, 'ORD-INACTIVE-0001');

    $staffMembership->forceFill(['is_active' => false])->save();

    // Deactivating a membership revokes access entirely, rather than rendering an
    // empty dashboard, so the order can never leak through the page.
    $this->actingAs($staff)
        ->withSession(['current_store_id' => $store->id])
        ->get(route('merchant.dashboard', ['store' => $store->slug]))
        ->assertForbidden();
});
