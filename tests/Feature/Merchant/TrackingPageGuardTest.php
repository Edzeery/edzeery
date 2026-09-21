<?php

use App\Enums\Store\StorePermissionEnum;
use App\Enums\Store\StoreRoleEnum;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Models\User;
use Database\Seeders\DemoStoreSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\StoreRolesAndPermissionsSeeder;
use Database\Seeders\SystemStatusesSeeder;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(StoreRolesAndPermissionsSeeder::class);
    $this->seed(SystemStatusesSeeder::class);
});

function guardMember(string $role, array $permissions): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate($role, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name'    => 'Guard Store',
        'slug'    => 'guard-'.uniqid(),
        'status'  => 'active',
    ]);

    $membership = StoreMembership::create([
        'store_id'   => $store->id,
        'user_id'    => $user->id,
        'invited_by' => $user->id,
        'is_active'  => true,
        'role'       => $role,
    ]);
    $membership->syncPermissions($permissions);

    return [$user, $store, $membership];
}

it('blocks a confirm-only staff member from the tracking page (Phase 36.9)', function () {
    [$user, $store] = guardMember(StoreRoleEnum::STAFF->value, [
        StorePermissionEnum::ORDER_VIEW->value,
        StorePermissionEnum::ORDER_CONFIRM->value,
    ]);

    actingAs($user);

    get(route('merchant.tracking.index', ['store' => $store->slug]))->assertForbidden();
});

it('lets a staff member holding CRM_ORDER_TRACKING open the tracking page', function () {
    [$user, $store] = guardMember(StoreRoleEnum::STAFF->value, [
        StorePermissionEnum::ORDER_VIEW->value,
        StorePermissionEnum::CRM_ORDER_TRACKING->value,
    ]);

    actingAs($user);

    get(route('merchant.tracking.index', ['store' => $store->slug]))->assertOk();
});

it('keeps owner, admin and manager on the tracking page via ORDER_MANAGE', function (string $role) {
    [$user, $store] = guardMember($role, StorePermissionEnum::values());

    actingAs($user);

    get(route('merchant.tracking.index', ['store' => $store->slug]))->assertOk();
})->with([
    'owner'   => StoreRoleEnum::OWNER->value,
    'admin'   => StoreRoleEnum::ADMIN->value,
    'manager' => StoreRoleEnum::MANAGER->value,
]);

it('reflects the demo accounts: tracker and dual can open tracking, confirmer cannot', function () {
    $this->seed(DemoStoreSeeder::class);

    $confirmer = User::where('email', 'demo.confirmer@edzeery.com')->firstOrFail();
    $tracker = User::where('email', 'demo.tracker@edzeery.com')->firstOrFail();
    $dual = User::where('email', 'demo.dual@edzeery.com')->firstOrFail();

    $store = $confirmer->stores()->first() ?? $tracker->stores()->first();

    actingAs($confirmer);
    get(route('merchant.tracking.index', ['store' => $store->slug]))->assertForbidden();

    actingAs($tracker);
    get(route('merchant.tracking.index', ['store' => $store->slug]))->assertOk();

    actingAs($dual);
    get(route('merchant.tracking.index', ['store' => $store->slug]))->assertOk();
});