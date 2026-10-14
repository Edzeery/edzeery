<?php

use App\Domains\Shipping\Models\ShippingProvider;
use App\Enums\Store\StorePermissionEnum;
use App\Enums\Store\StoreRoleEnum;
use App\Models\Orders\Order;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Support\StoreRoles;
use Carbon\CarbonImmutable;
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
    // PHASE 37-N6: freeze mid-day so "today" (Africa/Algiers) cannot drift off
    // an order's implicit created_at while the request is built.
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-03-10 12:00:00', 'UTC'));

    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(StoreRolesAndPermissionsSeeder::class);
    $this->seed(SystemStatusesSeeder::class);
    $this->seed(PlansSeeder::class);
});

afterEach(function () {
    CarbonImmutable::setTestNow();
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
 * A carrier that can be attached to an order, so it shows up in the filter list.
 */
function dfwCarrier(Store $store, string $name, bool $isActive = true): ShippingProvider
{
    return ShippingProvider::create([
        'store_id' => $store->id,
        'name' => $name,
        'code' => 'dfw-'.uniqid(),
        'credentials' => [],
        'is_active' => $isActive,
    ]);
}

/**
 * A delivered order, optionally routed through a carrier.
 */
function dfwOrder(Store $store, \App\Models\User $user, ?ShippingProvider $carrier = null): Order
{
    return Order::query()->create([
        'store_id' => $store->id,
        'number' => 'ORD-'.uniqid(),
        'total_amount' => 2500,
        'status_id' => app(\App\Domains\Analytics\Support\OrderStatusIdMap::class)
            ->id(\App\Enums\Store\OrderStatus::DELIVERED),
        'delivery_type' => 'home',
        'shipping_provider_id' => $carrier?->id,
        // PHASE 37-N6: deterministic timestamps for the default "today" window.
        'created_at' => now(),
        'updated_at' => now(),
    ]);
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

test('re-applying the active period leaves state and payload untouched', function () {
    [$user, $store] = dfwOwnerStore();

    $this->actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.dashboard')
        ->call('setPeriod', 'week')
        ->assertSet('period', 'week')
        ->call('setPeriod', 'week') // same value again: must be a no-op
        ->assertSet('period', 'week')
        ->assertSet('carrierId', null)
        ->assertSet('memberDimension', null)
        ->assertSet('dateFrom', null)
        ->assertSet('dateTo', null)
        ->assertNotDispatched('dashboard-filters-reset')
        // A real change still lands.
        ->call('setPeriod', 'all')
        ->assertSet('period', 'all');
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

    // The bar is included above the KPI region. The default (confirmation) view
    // drops the carrier control (D4) but keeps member and date basis.
    expect($html)
        ->not->toContain(__('dashboard.filter_carrier'))
        ->not->toContain(__('dashboard.all_carriers'))
        ->toContain(__('dashboard.filter_member'))
        ->toContain(__('dashboard.all_members'))
        ->toContain('wire:loading.class')
        ->toContain('wire:target="period,dateFrom,dateTo,carrierId,memberId,memberDimension,dateBasis,productIds,teamTrash,teamCohort,teamSearch,setConfirmationTab,setDeliveryTab,setTab"');

    // OWNER sees the member list, and holds both a confirmation capability and
    // STATS_DELIVERY, so all three view tabs are offered: the tab, not a member
    // pick, selects the whole dashboard view.
    expect($html)
        ->toContain('wire:model.live="memberId"')
        ->not->toContain('wire:model.live="carrierId"')
        ->toContain('wire:click="setTab(\'confirmation\')"')
        ->toContain('wire:click="setTab(\'delivery\')"')
        ->toContain('wire:click="setTab(\'top\')"')
        ->toContain('search')
        ->toContain(__('dashboard.tab_delivery'));

    // The same owner in the delivery view gets the carrier control back.
    $deliveryHtml = $this->actingAs($user)
        ->withSession(['current_store_id' => $store->id])
        ->get(route('merchant.dashboard', ['store' => $store->slug, 'md' => 'delivery']))
        ->assertOk()
        ->getContent();

    expect($deliveryHtml)
        ->toContain(__('dashboard.filter_carrier'))
        ->toContain(__('dashboard.all_carriers'))
        ->toContain('wire:model.live="carrierId"')
        ->toContain('wire:model.live="memberId"');

    $source = file_get_contents(
        resource_path('views/livewire/merchant/dashboard/partials/filter-bar.blade.php')
    );

    expect($source)
        ->not->toContain('DashboardFilterOptions')
        ->not->toContain('::query()')
        ->not->toContain('dark:');
});

test('the view tabs are offered without picking a member first', function () {
    [$owner, $store] = dfwOwnerStore();

    $this->actingAs($owner)->withSession(['current_store_id' => $store->id]);

    // No member is selected and none is needed: the view drives the KPI row and
    // the lists, so its tabs must not wait for a member filter to appear.
    Volt::test('merchant.dashboard')
        ->assertSet('memberId', null)
        ->assertSeeHtml('wire:click="setTab(\'confirmation\')"')
        ->assertSeeHtml('wire:click="setTab(\'delivery\')"')
        ->assertSeeHtml('wire:click="setTab(\'top\')"')
        ->assertSee(__('dashboard.tab_confirmation'))
        ->assertSee(__('dashboard.tab_delivery'))
        ->assertSee(__('dashboard.tab_top'));
});

test('the view tabs lead the page as a centred, icon-led control', function () {
    [$owner, $store] = dfwOwnerStore();

    $html = $this->actingAs($owner)
        ->withSession(['current_store_id' => $store->id])
        ->get(route('merchant.dashboard', ['store' => $store->slug]))
        ->assertOk()
        ->getContent();

    // The tabs come before the filter bar: the user picks the lens first, then
    // narrows it — reversing the old order where the filters led the page.
    expect(strpos($html, 'id="dashboard-tab-confirmation"'))
        ->toBeLessThan(strpos($html, 'wire:model.live="memberId"'));

    // Every permitted view is a tab wired to its own panel.
    expect($html)
        ->toContain('aria-controls="dashboard-panel-confirmation"')
        ->toContain('aria-controls="dashboard-panel-delivery"')
        ->toContain('aria-controls="dashboard-panel-top"');

    $source = file_get_contents(
        resource_path('views/livewire/merchant/dashboard/partials/tabs.blade.php')
    );

    expect($source)
        ->toContain('justify-center')
        ->toContain("'confirmation' => 'check-circle'")
        ->toContain("'delivery' => 'truck'")
        ->toContain("'top' => 'trending-up'")
        ->not->toContain('dark:')
        ->not->toContain('gray-');
});

test('switching the view tab swaps the KPI row and the lists', function () {
    [$owner, $store] = dfwOwnerStore();

    $this->actingAs($owner)->withSession(['current_store_id' => $store->id]);

    // Confirmation is the default for a user who can confirm: it leads with the
    // actionable states and shows the pending queue, never the delivery body.
    Volt::test('merchant.dashboard')
        ->assertSet('tab', null)
        ->assertSee(__('dashboard.pending_confirmation_count'))
        ->assertSee(__('dashboard.canceled_count'))
        ->assertSee(__('dashboard.pending_confirmation'))
        ->assertDontSee(__('dashboard.delivery_breakdown'))
        ->assertDontSee(__('dashboard.aov'));

    // Delivery swaps the queue for the delivery-rate cards; the where-are-the-
    // orders pair moved up to Top (PHASE 37-N7/D5).
    Volt::test('merchant.dashboard')
        ->call('setTab', 'delivery')
        ->assertSet('tab', 'delivery')
        ->assertSee(__('dashboard.delivery_rate_gross'))
        ->assertSee(__('dashboard.delivery_stats_title'))
        ->assertDontSee(__('dashboard.delivery_breakdown'))
        ->assertDontSee(__('dashboard.pending_confirmation_count'))
        ->assertDontSee(__('dashboard.canceled_count'))
        ->assertDontSee(__('dashboard.pending_confirmation'));

    // Top is the executive board: the four headline cards, the rankings and the
    // where-are-the-orders pair.
    Volt::test('merchant.dashboard')
        ->call('setTab', 'top')
        ->assertSet('tab', 'top')
        ->assertSee(__('dashboard.aov'))
        ->assertSee(__('dashboard.return_rate'))
        ->assertSee(__('dashboard.delivery_breakdown'))
        ->assertDontSee(__('dashboard.pending_confirmation_count'));
});

test('a user with only the confirmation capability gets a heading, not tabs', function () {
    [$owner, $store] = dfwOwnerStore();

    // STAFF carries STATS_CONFIRMATION but neither STATS_DELIVERY nor
    // STATS_TOP_KPIS, so exactly one view is permitted.
    $staff = roleUser('merchant');
    dfwMembership($store, $staff, StoreRoleEnum::STAFF);

    $html = $this->actingAs($staff)
        ->withSession(['current_store_id' => $store->id])
        ->get(route('merchant.dashboard', ['store' => $store->slug]))
        ->assertOk()
        ->getContent();

    // The single view is still labelled, but as a heading with no view tab
    // strip (the count/rate control is a separate, inner tablist).
    expect($html)
        ->toContain(__('dashboard.tab_confirmation'))
        ->not->toContain('id="dashboard-tab-')
        ->not->toContain('wire:click="setTab(');
});

test('the legacy md seed still selects the view for old links', function () {
    [$owner, $store] = dfwOwnerStore();

    $this->actingAs($owner)->withSession(['current_store_id' => $store->id]);

    // Pre-37-N5 links selected the whole view with ?md=delivery. The tab now
    // owns that state, but the old parameter seeds it so the link keeps working.
    // The delivery view is marked by its own carrier filter (D1).
    expect($this->actingAs($owner)
        ->withSession(['current_store_id' => $store->id])
        ->get(route('merchant.dashboard', ['store' => $store->slug, 'md' => 'delivery']))
        ->assertOk()
        ->getContent())
        ->toContain(__('dashboard.filter_carrier'));
});

test('the delivery team section offers a centred search and a cohort pick', function () {
    [$owner, $store] = dfwOwnerStore();

    // The tracking-team controls live on the delivery view (PHASE 37-N7/D8):
    // a debounced search, a cohort dropdown (all / with / without the
    // confirmation team) and the trash toggle.
    $html = $this->actingAs($owner)
        ->withSession(['current_store_id' => $store->id])
        ->get(route('merchant.dashboard', ['store' => $store->slug, 'md' => 'delivery']))
        ->assertOk()
        ->getContent();

    expect($html)
        ->toContain('wire:model.live.debounce.300ms="teamSearch"')
        ->toContain('wire:change="setTeamCohort($event.target.value)"')
        ->toContain('wire:model="teamCohort"')
        // The cohort options ride inside the custom select's JSON payload.
        ->toContain(__('dashboard.team_cohort_with'))
        ->toContain(__('dashboard.team_cohort_without'));

    // The setter narrows to a known cohort and ignores anything else.
    Volt::test('merchant.dashboard')
        ->call('setTab', 'delivery')
        ->call('setTeamCohort', 'with')
        ->assertSet('teamCohort', 'with')
        ->call('setTeamCohort', 'not-a-cohort')
        ->assertSet('teamCohort', 'with');
});

test('member select is hidden and the carrier stays away when the member is locked', function () {
    [$owner, $store] = dfwOwnerStore();

    // STAFF carries neither team-view nor team-view-own, so the factory locks
    // the scope to the current membership. STAFF also lacks STATS_DELIVERY, so
    // the delivery view (and its carrier filter) cannot be opened at all.
    $staff = roleUser('merchant');
    dfwMembership($store, $staff, StoreRoleEnum::STAFF);

    $html = $this->actingAs($staff)
        ->withSession(['current_store_id' => $store->id])
        ->get(route('merchant.dashboard', ['store' => $store->slug]))
        ->assertOk()
        ->getContent();

    expect($html)
        ->not->toContain('wire:model.live="memberId"')
        ->not->toContain('wire:model.live="carrierId"')
        // D4: the carrier filter is part of the delivery view only, so a
        // locked, confirmation-only user never sees it.
        ->not->toContain(__('dashboard.filter_carrier'));
});

test('the trend axes start at zero and stay whole numbers', function () {
    $source = file_get_contents(
        resource_path('views/livewire/merchant/dashboard/partials/charts.blade.php')
    );

    expect($source)
        // Both series are counts and sums, so neither axis has a negative half.
        ->toContain('beginAtZero: true')
        // y carries order counts, y1 only appears where a metric carries money.
        ->toContain('suggestedMax: hasPositive(yValues) ? undefined : 4')
        ->toContain('suggestedMax: hasPositive(y1Values) ? undefined : 1000')
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
    // the chart title carries the active period label for the active view.
    expect($html)
        ->toContain(__('dashboard.chart_confirmation_trend', ['period' => __('dashboard.period_today')]))
        ->toContain(__('dashboard.no_data'));

    $source = file_get_contents(
        resource_path('views/livewire/merchant/dashboard/partials/charts.blade.php')
    );

    // Client-side date parsing produced NaN labels; labels are display-ready
    // strings generated by DateBucket now.
    expect($source)
        ->not->toContain('new Date(')
        ->toContain('labels: data.chartLabels');
});

test('the carrier and member controls are the project select with a search field', function () {
    [$owner, $store] = dfwOwnerStore();

    $html = $this->actingAs($owner)
        ->withSession(['current_store_id' => $store->id])
        // The carrier control lives in the delivery view (D4), so that is the
        // view where both selects render next to each other.
        ->get(route('merchant.dashboard', ['store' => $store->slug, 'md' => 'delivery']))
        ->assertOk()
        ->getContent();

    // x-edz.select renders a combobox trigger plus its own search input, and
    // binds the model through the hidden input rather than a native <select>.
    expect($html)
        ->toContain('edz-select')
        ->toContain('role="combobox"')
        ->toContain('edz-select__search-input')
        ->toContain(__('dashboard.search_carrier'))
        ->toContain(__('dashboard.search_member'))
        // The translated empty state the panel shows when a search matches nothing.
        ->toContain(__('merchant_panel.no_options_found'))
        ->not->toContain('<select id="dashboard-carrier"')
        ->not->toContain('<select id="dashboard-member"');

    $source = file_get_contents(
        resource_path('views/livewire/merchant/dashboard/partials/filter-bar.blade.php')
    );

    // Both controls are live-bound so a pick applies without an explicit apply.
    expect($source)
        ->toContain('<x-edz.select')
        ->toContain('wire:model.live="carrierId"')
        ->toContain('wire:model.live="memberId"')
        ->toContain(":searchPlaceholder=\"__('dashboard.search_carrier')\"")
        ->toContain(":searchPlaceholder=\"__('dashboard.search_member')\"")
        // Options come from the class, never from view logic.
        ->toContain("\$filterOptions['carrierOptions']")
        ->toContain("\$filterOptions['memberOptions']");
});

test('the carrier select carries every provider with orders and hints the inactive ones', function () {
    [$owner, $store] = dfwOwnerStore();

    $active = dfwCarrier($store, 'Active Carrier');
    $inactive = dfwCarrier($store, 'Retired Carrier', isActive: false);

    // Only providers that actually appear on an order are offered.
    $order = dfwOrder($store, $owner, $active);

    $options = app(\App\Domains\Analytics\Support\DashboardFilterOptions::class)
        ->carrierSelectOptions($store->id);

    expect($options)->toBeArray()
        // The empty first entry is what the "all" pick writes to the model.
        ->and($options[0]['value'])->toBe('')
        ->and($options[0]['label'])->toBe(__('dashboard.all_carriers'))
        ->and(array_column($options, 'value'))->toContain((string) $active->id)
        ->and(array_column($options, 'value'))->not->toContain((string) $inactive->id);

    $activeOption = collect($options)->firstWhere('value', (string) $active->id);
    expect($activeOption['hint'])->toBeNull();

    // Now give the retired provider an order: it must now appear, flagged.
    $order->update(['shipping_provider_id' => $inactive->id]);

    $options = app(\App\Domains\Analytics\Support\DashboardFilterOptions::class)
        ->carrierSelectOptions($store->id);

    $inactiveOption = collect($options)->firstWhere('value', (string) $inactive->id);
    expect($inactiveOption['hint'])->toBe(__('dashboard.carrier_inactive'));
});

test('member select options follow the existing scope rules and hint the role', function () {
    [$owner, $store] = dfwOwnerStore();

    // A second, unrelated member the owner can see.
    $colleague = roleUser('merchant');
    dfwMembership($store, $colleague, StoreRoleEnum::MANAGER);

    $membership = $store->memberships()->where('user_id', $owner->id)->first();

    $options = app(\App\Domains\Analytics\Support\DashboardFilterOptions::class)
        ->memberSelectOptions($membership);

    expect($options)->toBeArray()
        ->and($options[0]['value'])->toBe('')
        ->and($options[0]['label'])->toBe(__('dashboard.all_members'))
        ->and(count($options))->toBeGreaterThan(1);

    // The role label is read from the membership row, so it costs no extra query.
    $ownerOption = collect($options)->firstWhere('value', $membership->id);
    expect($ownerOption['hint'])->toBe(StoreRoleEnum::OWNER->label());

    // A member with neither team-view nor team-view-own sees only themselves,
    // plus the «بلا رصيد» sentinel pick everyone can choose.
    $staff = roleUser('merchant');
    $staffMembership = dfwMembership($store, $staff, StoreRoleEnum::STAFF);

    $staffOptions = app(\App\Domains\Analytics\Support\DashboardFilterOptions::class)
        ->memberSelectOptions($staffMembership);

    expect($staffOptions)->toHaveCount(3)
        ->and(array_column($staffOptions, 'value'))
        ->toBe(['', $staffMembership->id, \App\Domains\Analytics\DTOs\DashboardFilter::UNATTRIBUTED]);

    // An inactive or absent membership yields the "all" entry too, but the
    // sentinel pick stays available, so it has no members to list.
    expect(app(\App\Domains\Analytics\Support\DashboardFilterOptions::class)->memberSelectOptions(null))
        ->toHaveCount(2)
        ->and(collect(app(\App\Domains\Analytics\Support\DashboardFilterOptions::class)->memberSelectOptions(null))
            ->pluck('value')->all())
        ->toBe(['', \App\Domains\Analytics\DTOs\DashboardFilter::UNATTRIBUTED]);
});

test('the delivery team cohort pick names exactly the tracking-only members', function () {
    [$owner, $store] = dfwOwnerStore();

    // Three STAFF members that differ only by which workflow they belong to.
    $confirmer = dfwMembership($store, roleUser('merchant'), StoreRoleEnum::STAFF);
    $confirmer->syncPermissions([StorePermissionEnum::ORDER_CONFIRM->value]);

    $tracker = dfwMembership($store, roleUser('merchant'), StoreRoleEnum::STAFF);
    $tracker->syncPermissions([StorePermissionEnum::CRM_ORDER_TRACKING->value]);

    $both = dfwMembership($store, roleUser('merchant'), StoreRoleEnum::STAFF);
    $both->syncPermissions([
        StorePermissionEnum::ORDER_CONFIRM->value,
        StorePermissionEnum::CRM_ORDER_TRACKING->value,
    ]);

    $ownerMembership = $store->memberships()->where('user_id', $owner->id)->first();
    $options = app(\App\Domains\Analytics\Support\DashboardFilterOptions::class);
    $ids = fn (string $cohort) => $options->members($ownerMembership, 'delivery', false, $cohort)->pluck('id')->all();

    // PHASE 37-N9: "without the confirmation team" is exactly the tracking-only
    // members — the work side the Delivery view now names.
    expect($ids('without'))->toContain($tracker->id)
        ->and($ids('without'))->not->toContain($confirmer->id)
        ->and($ids('without'))->not->toContain($both->id)
        ->and($ids('without'))->not->toContain($ownerMembership->id);

    // "With" is management plus the confirmation team.
    expect($ids('with'))->toContain($confirmer->id, $both->id, $ownerMembership->id)
        ->and($ids('with'))->not->toContain($tracker->id);
});

test('the aov card lives only in the top KPI row', function () {
    [$owner, $store] = dfwOwnerStore();
    $this->actingAs($owner)->withSession(['current_store_id' => $store->id]);

    // 37-N5 moved the four headline cards (AOV among them) into the Top view;
    // the Confirmation and Delivery bodies no longer carry them.
    $topHtml = $this->actingAs($owner)
        ->withSession(['current_store_id' => $store->id])
        ->get(route('merchant.dashboard', ['store' => $store->slug, 't' => 'top']))
        ->assertOk()
        ->getContent();

    $confirmationHtml = $this->actingAs($owner)
        ->withSession(['current_store_id' => $store->id])
        ->get(route('merchant.dashboard', ['store' => $store->slug, 't' => 'confirmation']))
        ->assertOk()
        ->getContent();

    expect($topHtml)
        ->toContain(__('dashboard.aov'))
        ->toContain('xl:grid-cols-4');

    // The confirmation row is the rate plus the pending queue.
    expect($confirmationHtml)
        ->not->toContain(__('dashboard.aov'))
        ->toContain('lg:grid-cols-3');
});

test('the trend draws straight segments with points only where there is activity', function () {
    $source = file_get_contents(
        resource_path('views/livewire/merchant/dashboard/partials/charts.blade.php')
    );

    // A smoothed curve between hourly buckets invents activity that never
    // happened; segments are straight and the orders line is not stepped.
    expect($source)
        ->toContain('tension: 0')
        ->not->toContain('tension: 0.4')
        ->toContain('stepped: false')
        // An axis of 31 buckets or fewer can carry markers; a wider one cannot.
        ->toContain('values.length <= 31')
        ->toContain('const pointHoverRadius = (values) => showPoints(values) ? 5 : 0;')
        // Hourly buckets are mostly empty, so a zero bucket gets no marker.
        ->toContain('isHourly(data.chartLabels) && !(Number(ctx.raw) > 0) ? 0 : 3')
        // The tooltip carries the bucket label and a formatted value.
        ->toContain("title: (items) => items.length ? items[0].label : ''")
        ->toContain('maximumFractionDigits: 0')
        // The money axis is y1; bare counts on y stay unformatted.
        ->toContain("context.dataset.yAxisID === 'y1'");
});

test('selecting all carrier clears the carrier filter', function () {
    [$user, $store] = dfwOwnerStore();

    $this->actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.dashboard')
        ->set('carrierId', '')
        ->assertSet('carrierId', null);
});

test('selecting all member clears the member filter', function () {
    [$user, $store] = dfwOwnerStore();

    $this->actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.dashboard')
        ->set('memberId', '')
        ->assertSet('memberId', null);
});
