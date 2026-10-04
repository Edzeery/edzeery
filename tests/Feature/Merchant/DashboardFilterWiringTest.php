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

    // OWNER sees the member list, and holds both a confirmation capability and
    // STATS_DELIVERY, so the stats view switch is offered straight away: it
    // selects the whole dashboard view, not just member attribution.
    expect($html)
        ->toContain('wire:model="memberId"')
        ->toContain('wire:model.live="memberDimension"')
        ->toContain(__('dashboard.stats_view'));

    $source = file_get_contents(
        resource_path('views/livewire/merchant/dashboard/partials/filter-bar.blade.php')
    );

    expect($source)
        ->not->toContain('DashboardFilterOptions')
        ->not->toContain('::query()')
        ->not->toContain('dark:');
});

test('the stats view switch is offered without picking a member first', function () {
    [$owner, $store] = dfwOwnerStore();

    $this->actingAs($owner)->withSession(['current_store_id' => $store->id]);

    // No member is selected and none is needed: the switch drives the KPI row
    // and the lists, so it must not wait for a member filter to appear.
    Volt::test('merchant.dashboard')
        ->assertSet('memberId', null)
        ->assertSeeHtml('wire:model.live="memberDimension"')
        ->assertSee(__('dashboard.dimension_confirmation'))
        ->assertSee(__('dashboard.dimension_delivery'));
});

test('switching the stats view swaps the KPI row and the lists', function () {
    [$owner, $store] = dfwOwnerStore();

    $this->actingAs($owner)->withSession(['current_store_id' => $store->id]);

    // Confirmation is the default for a user who can confirm: it leads with the
    // two actionable states and shows the pending queue.
    Volt::test('merchant.dashboard')
        ->assertSee(__('dashboard.pending_confirmation_count'))
        ->assertSee(__('dashboard.canceled_count'))
        ->assertSee(__('dashboard.pending_confirmation'))
        ->assertDontSee(__('dashboard.revenue_delivered_in_period'))
        ->assertDontSee(__('dashboard.delivery_breakdown'));

    // Switching to delivery replaces both.
    Volt::test('merchant.dashboard')
        ->set('memberDimension', 'delivery')
        ->assertSet('memberDimension', 'delivery')
        ->assertSee(__('dashboard.revenue_delivered_in_period'))
        ->assertSee(__('dashboard.return_rate'))
        ->assertSee(__('dashboard.delivery_breakdown'))
        ->assertDontSee(__('dashboard.pending_confirmation_count'))
        ->assertDontSee(__('dashboard.canceled_count'))
        ->assertDontSee(__('dashboard.pending_confirmation'));

    // Back again, proving the switch is not one-way.
    Volt::test('merchant.dashboard')
        ->set('memberDimension', 'confirmation')
        ->assertSee(__('dashboard.pending_confirmation_count'))
        ->assertDontSee(__('dashboard.delivery_breakdown'));
});

test('a user with only the confirmation capability gets a fixed badge, not a switch', function () {
    [$owner, $store] = dfwOwnerStore();

    // STAFF carries STATS_CONFIRMATION but not STATS_DELIVERY.
    $staff = roleUser('merchant');
    dfwMembership($store, $staff, StoreRoleEnum::STAFF);

    $html = $this->actingAs($staff)
        ->withSession(['current_store_id' => $store->id])
        ->get(route('merchant.dashboard', ['store' => $store->slug]))
        ->assertOk()
        ->getContent();

    // The view is still labelled, just not selectable.
    expect($html)
        ->toContain(__('dashboard.stats_view'))
        ->toContain(__('dashboard.dimension_confirmation'))
        ->not->toContain('wire:model.live="memberDimension"');
});

test('the stats view is only chipped once it differs from the user default', function () {
    [$owner, $store] = dfwOwnerStore();

    $this->actingAs($owner)->withSession(['current_store_id' => $store->id]);

    // OWNER defaults to confirmation, so asking for confirmation is not an
    // override and nothing is chipped.
    expect($this->actingAs($owner)
        ->withSession(['current_store_id' => $store->id])
        ->get(route('merchant.dashboard', ['store' => $store->slug, 'md' => 'confirmation']))
        ->assertOk()
        ->getContent())
        ->not->toContain(__('dashboard.active_filters'));

    // Delivery is the override, so it is surfaced with a reset affordance.
    expect($this->actingAs($owner)
        ->withSession(['current_store_id' => $store->id])
        ->get(route('merchant.dashboard', ['store' => $store->slug, 'md' => 'delivery']))
        ->assertOk()
        ->getContent())
        ->toContain(__('dashboard.active_filters'))
        ->toContain(__('dashboard.dimension_delivery'))
        ->toContain(__('dashboard.reset_filters'));
});

test('member select is hidden and the dimension is fixed when the member is locked', function () {
    [$owner, $store] = dfwOwnerStore();

    // STAFF carries neither team-view nor team-view-own, so the factory locks
    // the scope to the current membership. STAFF also lacks STATS_DELIVERY, so
    // the stats view cannot be switched at all.
    $staff = roleUser('merchant');
    dfwMembership($store, $staff, StoreRoleEnum::STAFF);

    $html = $this->actingAs($staff)
        ->withSession(['current_store_id' => $store->id])
        ->get(route('merchant.dashboard', ['store' => $store->slug]))
        ->assertOk()
        ->getContent();

    expect($html)
        ->not->toContain('wire:model="memberId"')
        ->not->toContain('wire:model.live="memberDimension"')
        // The locked dimension is still reported, just not selectable.
        ->toContain(__('dashboard.dimension_confirmation'))
        ->toContain(__('dashboard.filter_carrier'));
});

test('the trend axes start at zero and stay whole numbers', function () {
    $source = file_get_contents(
        resource_path('views/livewire/merchant/dashboard/partials/charts.blade.php')
    );

    expect($source)
        // Both series are counts and sums, so neither axis has a negative half.
        ->toContain('beginAtZero: true')
        ->toContain('suggestedMax: hasPositive(data.chartRevenue) ? undefined : 1000')
        ->toContain('suggestedMax: hasPositive(data.chartOrders) ? undefined : 4')
        // "2.5 orders" is not a thing.
        ->toContain('precision: 0');
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
