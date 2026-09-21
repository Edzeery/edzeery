<?php

use App\Enums\Store\StoreRoleEnum;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Models\User;
use App\Services\Stores\StoreTeamService;
use App\Support\StoreRoles;
use Database\Seeders\DemoStoreSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\StoreRolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Livewire\Volt\Volt;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(StoreRolesAndPermissionsSeeder::class);
    Mail::fake();
});

function hierarchyStore(User $owner, string $suffix): Store
{
    return Store::create([
        'user_id' => $owner->id,
        'name'    => "Hierarchy Store {$suffix}",
        'slug'    => "hierarchy-store-{$suffix}-".uniqid(),
        'status'  => 'active',
    ]);
}

function hierarchyMembership(Store $store, User $user, User $inviter, StoreRoleEnum $role): StoreMembership
{
    return StoreMembership::create([
        'store_id'   => $store->id,
        'user_id'    => $user->id,
        'invited_by' => $inviter->id,
        'is_active'  => true,
        'role'       => $role->value,
    ]);
}

function hierarchyActAs(User $user, Store $store): void
{
    test()->actingAs($user);
    test()->withSession(['current_store_id' => $store->id]);
    app(\App\Support\StoreContext::class)->clear();
}

it('auto-routes a staff member added by an active manager to that manager (rule b)', function () {
    $owner = roleUser('merchant');
    $store = hierarchyStore($owner, 'Auto');

    $manager = roleUser('merchant');
    $managerMembership = hierarchyMembership($store, $manager, $owner, StoreRoleEnum::MANAGER);

    hierarchyActAs($manager, $store);

    $staffUser = roleUser('merchant');
    $member = app(StoreTeamService::class)->addMember($store, [
        'name'       => 'New Staff',
        'email'      => 'staff.auto@example.com',
        'password'   => 'password123',
        'store_role' => StoreRoleEnum::STAFF->value,
        'is_active'  => true,
    ]);

    expect($member->supervisor_membership_id)->toBe($managerMembership->id)
        ->and($member->fresh()->supervisor()?->first()?->id)->toBe($managerMembership->id);
});

it('attaches a valid explicit supervisor for the owner and rejects every invalid one (rules c/d)', function () {
    $owner = roleUser('merchant');
    $store = hierarchyStore($owner, 'Explicit');
    hierarchyMembership($store, $owner, $owner, StoreRoleEnum::OWNER);

    $manager = roleUser('merchant');
    $validManagerMembership = hierarchyMembership($store, $manager, $owner, StoreRoleEnum::MANAGER);

    // Different store → same-store check fails.
    $otherStore = hierarchyStore($owner, 'Other');
    $otherManager = roleUser('merchant');
    $otherStoreMembership = hierarchyMembership($otherStore, $otherManager, $owner, StoreRoleEnum::MANAGER);

    // Member exists in this store but is not a manager.
    $staff = roleUser('merchant');
    $staffMembership = hierarchyMembership($store, $staff, $owner, StoreRoleEnum::STAFF);

    // Manager of this store but not active.
    $dormantManager = roleUser('merchant');
    $dormantManagerMembership = StoreMembership::create([
        'store_id'   => $store->id,
        'user_id'    => $dormantManager->id,
        'invited_by' => $owner->id,
        'is_active'  => false,
        'role'       => StoreRoleEnum::MANAGER->value,
    ]);

    hierarchyActAs($owner, $store);

    $member = app(StoreTeamService::class)->addMember($store, [
        'name'                      => 'New Staff',
        'email'                     => 'staff.valid@example.com',
        'password'                  => 'password123',
        'store_role'                => StoreRoleEnum::STAFF->value,
        'supervisor_membership_id'  => $validManagerMembership->id,
        'is_active'                 => true,
    ]);

    expect($member->supervisor_membership_id)->toBe($validManagerMembership->id);

    foreach ([$otherStoreMembership, $staffMembership, $dormantManagerMembership] as $candidate) {
        $staffUser = roleUser('merchant');

        expect(fn () => app(StoreTeamService::class)->addMember($store, [
            'name'                      => 'New Staff',
            'email'                     => 'staff.reject-'.Str::random(8).'@example.com',
            'password'                  => 'password123',
            'store_role'                => StoreRoleEnum::STAFF->value,
            'supervisor_membership_id'  => $candidate->id,
            'is_active'                 => true,
        ]))->toThrow(\Exception::class, __('teams.invalid_supervisor'));
    }
});

it('silently ignores any supervisor attempt made by a non-owner/admin (rule c)', function () {
    $owner = roleUser('merchant');
    $store = hierarchyStore($owner, 'Imposter');
    hierarchyMembership($store, $owner, $owner, StoreRoleEnum::OWNER);

    $manager = roleUser('merchant');
    $managerMembership = hierarchyMembership($store, $manager, $owner, StoreRoleEnum::MANAGER);

    $otherManager = roleUser('merchant');
    $otherManagerMembership = hierarchyMembership($store, $otherManager, $owner, StoreRoleEnum::MANAGER);

    hierarchyActAs($manager, $store);

    // Forged explicit supervisor on create → silently unrouted (no throw).
    $staffUser = roleUser('merchant');
    $member = app(StoreTeamService::class)->addMember($store, [
        'name'                      => 'New Staff',
        'email'                     => 'staff.imposter@example.com',
        'password'                  => 'password123',
        'store_role'                => StoreRoleEnum::STAFF->value,
        'supervisor_membership_id'  => $otherManagerMembership->id,
        'is_active'                 => true,
    ]);

    expect($member->supervisor_membership_id)->toBeNull();

    // Forged reassignment on update → the stored supervisor stays untouched.
    $staffMembership = hierarchyMembership($store, $staffUser, $owner, StoreRoleEnum::STAFF);
    $staffMembership->update(['supervisor_membership_id' => $managerMembership->id]);

    app(StoreTeamService::class)->updateMember($store, $staffMembership, [
        'name'                      => 'Updated Staff',
        'email'                     => $staffUser->email,
        'store_role'                => StoreRoleEnum::STAFF->value,
        'supervisor_membership_id'  => $otherManagerMembership->id,
        'is_active'                 => true,
    ]);

    expect($staffMembership->fresh()->supervisor_membership_id)->toBe($managerMembership->id);
});

it('clears the supervisor when a member is moved out of the staff role (rule e)', function () {
    $owner = roleUser('merchant');
    $store = hierarchyStore($owner, 'Promote');
    hierarchyMembership($store, $owner, $owner, StoreRoleEnum::OWNER);

    $manager = roleUser('merchant');
    $managerMembership = hierarchyMembership($store, $manager, $owner, StoreRoleEnum::MANAGER);

    $staffUser = roleUser('merchant');
    $staffMembership = hierarchyMembership($store, $staffUser, $owner, StoreRoleEnum::STAFF);
    $staffMembership->update(['supervisor_membership_id' => $managerMembership->id]);

    hierarchyActAs($owner, $store);

    app(StoreTeamService::class)->updateMember($store, $staffMembership, [
        'name'                      => $staffUser->name,
        'email'                     => $staffUser->email,
        'store_role'                => StoreRoleEnum::ADMIN->value,
        'supervisor_membership_id'  => $managerMembership->id,
        'is_active'                 => true,
    ]);

    expect($staffMembership->fresh()->supervisor_membership_id)->toBeNull();
});

it('grants a manager only the members supervised through memberships, not the inviter', function () {
    $owner = roleUser('merchant');
    $store = hierarchyStore($owner, 'Manage');
    hierarchyMembership($store, $owner, $owner, StoreRoleEnum::OWNER);

    $manager = roleUser('merchant');
    $managerMembership = hierarchyMembership($store, $manager, $owner, StoreRoleEnum::MANAGER);

    // A second manager acts as the disconnected counter-example.
    $director = roleUser('merchant');
    $directorMembership = hierarchyMembership($store, $director, $owner, StoreRoleEnum::MANAGER);

    // Staff supervised by the manager, even though the OWNER invited them.
    $staffUser = roleUser('merchant');
    $staffMembership = hierarchyMembership($store, $staffUser, $owner, StoreRoleEnum::STAFF);
    $staffMembership->update(['supervisor_membership_id' => $managerMembership->id]);

    // Legacy anchor: invited by the manager, but not supervised by them.
    $orphanUser = roleUser('merchant');
    $orphanMembership = hierarchyMembership($store, $orphanUser, $manager, StoreRoleEnum::STAFF);

    hierarchyActAs($manager, $store);

    expect(managesMember($staffMembership))->toBeTrue()
        ->and(managesMember($orphanMembership))->toBeFalse()
        ->and(managesMember($directorMembership))->toBeFalse();
});

it('runs the supervisor migration cleanly with the demo seed and leaves demo staff unrouted by default', function () {
    $this->seed(DemoStoreSeeder::class);

    $store = Store::where('slug', 'demo')->first();

    expect($store)->not->toBeNull();

    $staff = StoreMembership::where('store_id', $store->id)
        ->where('role', StoreRoleEnum::STAFF->value)
        ->get();

    expect($staff)->not->toBeEmpty()
        ->and($staff->every(fn (StoreMembership $m) => $m->supervisor_membership_id === null))->toBeTrue()
        ->and(Schema::hasColumn('store_memberships', 'supervisor_membership_id'))->toBeTrue();
});

it('only shows the reports-to control to an owner/admin picking a staff role', function () {
    $owner = roleUser('merchant');
    $store = hierarchyStore($owner, 'Ui');
    hierarchyMembership($store, $owner, $owner, StoreRoleEnum::OWNER)
        ->syncPermissions(StoreRoles::permissions(StoreRoleEnum::OWNER));

    $manager = roleUser('merchant');
    $manager->update(['name' => 'Report Manager']);
    hierarchyMembership($store, $manager, $owner, StoreRoleEnum::MANAGER)
        ->syncPermissions(StoreRoles::permissions(StoreRoleEnum::MANAGER));

    hierarchyActAs($owner, $store);

    Volt::test('merchant.teams.index')
        ->assertOk()
        ->assertDontSee('Reports to')
        ->call('openCreate')
        ->set('store_role', StoreRoleEnum::STAFF->value)
        ->assertSee('Reports to')
        ->assertSee(__('teams.no_supervisor'))
        ->assertSee('Report Manager')
        ->set('store_role', StoreRoleEnum::ADMIN->value)
        ->assertDontSee('Reports to');

    // A manager actor never gets the control, even when editing a staff role.
    $otherOwner = roleUser('merchant');
    $otherStore = hierarchyStore($otherOwner, 'Ui2');
    hierarchyMembership($otherStore, $otherOwner, $otherOwner, StoreRoleEnum::OWNER)
        ->syncPermissions(StoreRoles::permissions(StoreRoleEnum::OWNER));

    $actorManager = roleUser('merchant');
    hierarchyMembership($otherStore, $actorManager, $otherOwner, StoreRoleEnum::MANAGER)
        ->syncPermissions(StoreRoles::permissions(StoreRoleEnum::MANAGER));

    hierarchyActAs($actorManager, $otherStore);

    Volt::test('merchant.teams.index')
        ->call('openCreate')
        ->set('store_role', StoreRoleEnum::STAFF->value)
        ->assertDontSee('Reports to');
});