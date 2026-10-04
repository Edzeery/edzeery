<?php

use App\Enums\Store\StoreRoleEnum;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Support\StoreRoles;
use Database\Seeders\PlansSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\StoreRolesAndPermissionsSeeder;
use Database\Seeders\SystemStatusesSeeder;
use Livewire\Volt\Volt;
use Spatie\Permission\Models\Role;

/*
|--------------------------------------------------------------------------
| PHASE 37-E - dashboard filter wiring
|--------------------------------------------------------------------------
|
| The dashboard imported DashboardFilterConcern but never registered it with
| uses(), so none of the URL-backed filter props existed and every analytics
| call ran unfiltered. The filter-bar partial was also never included and it
| resolved its own carrier/member options inside the view.
|
| These tests pin the wiring: the concern is active, one normalized filter is
| applied, the bar renders from pre-built options, and the permission gates
| around the member/dimension controls hold.
|
*/

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(StoreRolesAndPermissionsSeeder::class);
    $this->seed(SystemStatusesSeeder::class);
    $this->seed(PlansSeeder::class);
});

/**
 * A store owned by a full-permission OWNER, plus the owner's membership.
 *
 * @return array{0: \App\Models\User, 1: \App\Models\Stores\Store}
 */
function dfwOwnerStore(): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate(StoreRoleEnum::OWNER->value, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Filter Wiring Store',
        'slug' => 'filter-wiring-'.uniqid(),
        'status' => 'active',
    ]);

    dfwMembership($store, $user, StoreRoleEnum::OWNER);

    return [$user, $store];
}

/**
 * Attach a membership to an existing store with the given role's permissions.
 */
function dfwMembership(Store $store, \App\Models\User $user, StoreRoleEnum $role): StoreMembership
{
    // EnsureHasStoreRole reads the Spatie role off the user on the `merchant`
    // guard, not off the membership.
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

test('dashboard filter props are reactive and reset restores the default period', function () {
    [$user, $store] = dfwOwnerStore();

    $this->actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.dashboard')
        ->assertSet('period', 'today')
        ->assertSee(__('dashboard.period_today'))
        ->set('period', 'yesterday')
        ->assertSet('period', 'yesterday')
        ->assertSee(__('dashboard.period_yesterday'))
        ->call('resetFilters')
        ->assertSet('period', 'today')
        ->assertSet('carrierId', null)
        ->assertSet('memberId', null)
        ->assertSet('memberDimension', null)
        ->assertSet('dateFrom', null)
        ->assertSet('dateTo', null);
});

test('dashboard applies filters from the url query string', function () {
    [$user, $store] = dfwOwnerStore();

    $html = $this->actingAs($user)
        ->withSession(['current_store_id' => $store->id])
        ->get(route('merchant.dashboard', ['store' => $store->slug, 'p' => 'week']))
        ->assertOk()
        ->getContent();

    // The "Last 7 days" pill is selected and surfaced as an active filter.
    expect($html)
        ->toContain(__('dashboard.period_week'))
        ->toContain(__('dashboard.active_filters'))
        ->toContain(__('dashboard.reset_filters'));
});

test('dashboard renders the filter bar from pre-built options without querying in the view', function () {
    [$user, $store] = dfwOwnerStore();

    $html = $this->actingAs($user)
        ->withSession(['current_store_id' => $store->id])
        ->get(route('merchant.dashboard', ['store' => $store->slug]))
        ->assertOk()
        ->getContent();

    // The bar is included above the KPI region.
    expect($html)
        ->toContain(__('dashboard.filter_carrier'))
        ->toContain(__('dashboard.all_carriers'))
        ->toContain(__('dashboard.filter_member'))
        ->toContain(__('dashboard.all_members'))
        ->toContain('wire:loading.class')
        ->toContain('wire:target="period,dateFrom,dateTo,carrierId,memberId,memberDimension"');

    // OWNER sees the member list, but no dimension radios until a member is
    // picked (memberId defaults to null for a team-view permission).
    expect($html)
        ->toContain('wire:model="memberId"')
        ->not->toContain('wire:model="memberDimension"');

    $source = file_get_contents(
        resource_path('views/livewire/merchant/dashboard/partials/filter-bar.blade.php')
    );

    expect($source)
        ->not->toContain('DashboardFilterOptions')
        ->not->toContain('::query()')
        ->not->toContain('dark:');
});

test('dimension radios appear once a member is selected', function () {
    [$owner, $store] = dfwOwnerStore();

    $teammate = roleUser('merchant');
    dfwMembership($store, $teammate, StoreRoleEnum::MANAGER);

    $this->actingAs($owner)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.dashboard')
        ->set('memberId', $teammate->storeMemberships()->where('store_id', $store->id)->value('id'))
        ->assertSeeHtml('wire:model="memberDimension"')
        ->assertSee(__('dashboard.dimension_confirmation'))
        ->assertSee(__('dashboard.dimension_delivery'));
});

test('member select is hidden and the dimension is fixed when the member is locked', function () {
    [$owner, $store] = dfwOwnerStore();

    // STAFF carries neither team-view nor team-view-own, so the factory locks
    // the scope to the current membership. STAFF also lacks STATS_DELIVERY,
    // so the dimension radios must not render at all.
    $staff = roleUser('merchant');
    dfwMembership($store, $staff, StoreRoleEnum::STAFF);

    $html = $this->actingAs($staff)
        ->withSession(['current_store_id' => $store->id])
        ->get(route('merchant.dashboard', ['store' => $store->slug]))
        ->assertOk()
        ->getContent();

    expect($html)
        ->not->toContain('wire:model="memberId"')
        ->not->toContain('wire:model="memberDimension"')
        // The locked dimension is still reported, just not selectable.
        ->toContain(__('dashboard.dimension_confirmation'))
        ->toContain(__('dashboard.filter_carrier'));
});

test('a custom range without both dates reports that it was adjusted', function () {
    [$user, $store] = dfwOwnerStore();

    $this->actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.dashboard')
        ->set('period', 'custom')
        ->assertSee(__('dashboard.date_from'))
        ->assertSee(__('dashboard.date_to'))
        // No dates supplied, so the factory falls back to today.
        ->assertSee(__('dashboard.invalid_range'));

    Volt::test('merchant.dashboard')
        ->set('period', 'custom')
        ->set('dateFrom', '2026-01-01')
        ->set('dateTo', '2026-01-31')
        ->assertDontSee(__('dashboard.invalid_range'));
});

test('the sales trend uses server generated labels and a no-data state', function () {
    [$user, $store] = dfwOwnerStore();

    $html = $this->actingAs($user)
        ->withSession(['current_store_id' => $store->id])
        ->get(route('merchant.dashboard', ['store' => $store->slug]))
        ->assertOk()
        ->getContent();

    // No orders exist yet, so the empty series renders the no-data state and
    // the chart title carries the active period label.
    expect($html)
        ->toContain(__('dashboard.sales_trend_for', ['period' => __('dashboard.period_today')]))
        ->toContain(__('dashboard.no_data'));

    $source = file_get_contents(
        resource_path('views/livewire/merchant/dashboard/partials/charts.blade.php')
    );

    // Client-side date parsing produced NaN labels; labels are display-ready
    // strings generated by DateBucket now.
    expect($source)
        ->not->toContain('new Date(')
        ->toContain('labels: data.chartDays');
});
