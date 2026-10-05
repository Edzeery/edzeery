<?php

use App\Domains\Analytics\Services\StoreDashboardAnalyticsService;
use App\Domains\Analytics\Support\DashboardFilterFactory;
use App\Domains\Analytics\Support\OrderStatusIdMap;
use App\Domains\Shipping\Models\ShippingProvider;
use App\Enums\Store\OrderStatus;
use App\Enums\Store\StorePermissionEnum;
use App\Enums\Store\StoreRoleEnum;
use App\Models\Orders\Order;
use App\Models\Orders\OrderTracking;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Models\User;
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
| PHASE 37-J - team performance table
|--------------------------------------------------------------------------
|
| The table is one aggregate query plus a view model, so these tests drive the
| rendered page rather than the classes: both stats views, the period and
| carrier windows, the scoping rules the permission model produces, and the
| promise that an order with several tracking attempts is still one order.
|
*/

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(StoreRolesAndPermissionsSeeder::class);
    $this->seed(SystemStatusesSeeder::class);
    $this->seed(PlansSeeder::class);

    // Algiers is UTC+1 all year round, so "today" below is a fixed window.
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-03-10 12:00:00', 'UTC'));
});

afterEach(function () {
    CarbonImmutable::setTestNow();
});

/** A store owned by a full-permission owner, plus that owner's membership. */
function tpfStore(): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate(StoreRoleEnum::OWNER->value, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Team Performance Store',
        'slug' => 'team-performance-'.uniqid(),
        'status' => 'active',
    ]);

    tpfMembership($store, $user, StoreRoleEnum::OWNER);

    return [$user, $store];
}

/**
 * A membership for its own user. Permissions default to the role template so a
 * test that needs one branch can subtract exactly the permission it cares about.
 */
function tpfMembership(
    Store $store,
    ?User $user,
    StoreRoleEnum $role,
    ?array $permissions = null,
    ?StoreMembership $supervisor = null,
): StoreMembership {
    $user ??= roleUser('merchant');
    $user->assignRole(Role::findOrCreate($role->value, 'merchant'));

    $membership = StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'invited_by' => $store->user_id,
        'is_active' => true,
        'role' => $role->value,
        'supervisor_membership_id' => $supervisor?->id,
    ]);

    $membership->syncPermissions($permissions ?? StoreRoles::permissions($role));

    return $membership;
}

function tpfCarrier(Store $store, string $name): ShippingProvider
{
    return ShippingProvider::create([
        'store_id' => $store->id,
        'name' => $name,
        'code' => 'tpf-'.uniqid(),
        'credentials' => [],
        'is_active' => true,
    ]);
}

function tpfStatus(OrderStatus $status): string
{
    return app(OrderStatusIdMap::class)->id($status);
}

/** An order inside today's window unless told otherwise. */
function tpfOrder(Store $store, array $attributes = []): Order
{
    $createdAt = $attributes['created_at'] ?? CarbonImmutable::parse('2026-03-10 08:00:00', 'UTC');
    unset($attributes['created_at']);

    $order = Order::query()->create(array_merge([
        'store_id' => $store->id,
        'number' => 'ORD-'.uniqid(),
        'total_amount' => 100,
        'status_id' => tpfStatus(OrderStatus::PENDING),
        'delivery_type' => 'home',
    ], $attributes));

    $order->forceFill(['created_at' => $createdAt, 'updated_at' => $createdAt])->save();

    return $order;
}

/**
 * A tracking attempt. Explicit ULIDs keep the older/newer pair unambiguous:
 * ULIDs sort by time, so the newest attempt is simply the largest id.
 */
function tpfTracking(Store $store, Order $order, ?StoreMembership $assignedTo, string $id): OrderTracking
{
    $tracking = new OrderTracking([
        'store_id' => $store->id,
        'order_id' => $order->id,
        'assigned_to_membership_id' => $assignedTo?->id,
    ]);

    $tracking->id = $id;
    $tracking->save();

    return $tracking;
}

function tpfHtml(User $user, Store $store, array $query = []): string
{
    return test()->actingAs($user)
        ->withSession(['current_store_id' => $store->id])
        ->get(route('merchant.dashboard', $query + ['store' => $store->slug]))
        ->assertOk()
        ->getContent();
}

/** The KPI summary behind the cards, built the way the page builds it. */
function tpfSummary(User $user, Store $store): array
{
    $membership = $user->storeMemberships()->where('store_id', $store->id)->firstOrFail();

    return (new StoreDashboardAnalyticsService($store->id))->summary(
        app(DashboardFilterFactory::class)->make(['period' => 'today'], $membership),
    );
}

/**
 * The cells of the first rate-bearing table row containing the needle, or []
 * when the table has no such row. Restricting the search to rows with a percent
 * keeps the member <option> list and the other tables out of the answer.
 *
 * @return array<int, string>
 */
function tpfRow(string $html, string $needle): array
{
    preg_match_all('#<tr\b.*?</tr>#s', $html, $rows);

    foreach ($rows[0] as $row) {
        $text = trim(preg_replace('/\s+/', ' ', strip_tags($row)));

        if (! str_contains($text, '%') || ! str_contains($text, $needle)) {
            continue;
        }

        preg_match_all('#<t[dh][^>]*>(.*?)</t[dh]>#s', $row, $cells);

        return array_values(array_map(
            fn (string $cell) => trim(preg_replace('/\s+/', ' ', strip_tags($cell))),
            $cells[1],
        ));
    }

    return [];
}

/**
 * The shared fixture: five orders today across three members and one unowned,
 * plus yesterday's order for the period test and two carriers.
 *
 * Confirmation view reads assigned/confirmed/pending/canceled/other; delivery
 * view reads the same orders through their tracking rows, one of which has two
 * attempts so a re-shipped order would be counted twice by a naive join.
 *
 * @return array<string, mixed>
 */
function tpfScenario(): array
{
    [$user, $store] = tpfStore();

    $alpha = tpfMembership($store, null, StoreRoleEnum::STAFF);
    $beta = tpfMembership($store, null, StoreRoleEnum::STAFF);
    $gamma = tpfMembership($store, null, StoreRoleEnum::STAFF);

    tpfRename($alpha, 'Alpha');
    tpfRename($beta, 'Beta');
    tpfRename($gamma, 'Gamma');

    $carrierA = tpfCarrier($store, 'Carrier A');
    $carrierB = tpfCarrier($store, 'Carrier B');

    $delivered = fn (int $amount, ?ShippingProvider $carrier, ?StoreMembership $member, ?string $createdAt = null) => tpfOrder($store, [
        'status_id' => tpfStatus(OrderStatus::DELIVERED),
        'total_amount' => $amount,
        'shipping_provider_id' => $carrier?->id,
        'assigned_to_membership_id' => $member?->id,
        'created_at' => $createdAt ? CarbonImmutable::parse($createdAt, 'UTC') : null,
    ]);

    $orders = [
        // Alpha: confirmed, then delivered with two tracking attempts.
        'alphaConfirmed' => tpfOrder($store, [
            'status_id' => tpfStatus(OrderStatus::CONFIRMED),
            'shipping_provider_id' => $carrierA->id,
            'assigned_to_membership_id' => $alpha->id,
        ]),
        'alphaDelivered' => $delivered(300, $carrierA, $alpha),
        // Beta: one still pending, one returned.
        'betaPending' => tpfOrder($store, [
            'status_id' => tpfStatus(OrderStatus::PENDING),
            'total_amount' => 200,
            'shipping_provider_id' => $carrierB->id,
            'assigned_to_membership_id' => $beta->id,
        ]),
        'betaReturned' => tpfOrder($store, [
            'status_id' => tpfStatus(OrderStatus::RETURNED),
            'total_amount' => 60,
            'shipping_provider_id' => $carrierB->id,
            'assigned_to_membership_id' => $beta->id,
        ]),
        // Nobody owns this one, and it is cancelled.
        'unowned' => tpfOrder($store, [
            'status_id' => tpfStatus(OrderStatus::CANCELED),
            'total_amount' => 50,
            'shipping_provider_id' => $carrierA->id,
        ]),
        // Gamma only shows up outside today.
        'gammaYesterday' => tpfOrder($store, [
            'status_id' => tpfStatus(OrderStatus::DELIVERED),
            'total_amount' => 400,
            'assigned_to_membership_id' => $gamma->id,
            'created_at' => CarbonImmutable::parse('2026-03-09 08:00:00', 'UTC'),
        ]),
    ];

    tpfTracking($store, $orders['alphaConfirmed'], $alpha, '01J000000000000000000000AA');
    tpfTracking($store, $orders['betaPending'], $beta, '01J000000000000000000000BB');
    tpfTracking($store, $orders['betaReturned'], $beta, '01J000000000000000000000CC');
    // Re-shipped: first attempt went to Beta, the newest attempt is Alpha's.
    tpfTracking($store, $orders['alphaDelivered'], $beta, '01J000000000000000000000D1');
    tpfTracking($store, $orders['alphaDelivered'], $alpha, '01J000000000000000000000D2');
    tpfTracking($store, $orders['gammaYesterday'], $gamma, '01J000000000000000000000EE');

    return [
        'user' => $user, 'store' => $store,
        'alpha' => $alpha, 'beta' => $beta, 'gamma' => $gamma,
        'carrierA' => $carrierA, 'carrierB' => $carrierB,
        'orders' => $orders,
    ];
}

/** Memberships carry no display name column; the viewer's user owns it. */
function tpfRename(StoreMembership $membership, string $name): void
{
    User::query()->whereKey($membership->user_id)->update(['name' => $name]);
}

test('the confirmation view lists each member, the unowned orders and a total', function () {
    $f = tpfScenario();

    $html = tpfHtml($f['user'], $f['store']);

    expect($html)
        ->toContain(__('dashboard.team_performance_title'))
        ->toContain(__('dashboard.team_performance_confirmation'));

    // member, assigned, confirmed, pending, canceled, other, confirmation rate.
    expect(tpfRow($html, 'Alpha'))->toBe(['Alpha', '2', '2', '0', '0', '0', '100%'])
        ->and(tpfRow($html, 'Beta'))->toBe(['Beta', '2', '0', '1', '0', '1', '0%'])
        // The returned order is not confirmed, not pending and not cancelled,
        // so it lands in Other instead of vanishing from the row.
        ->and(tpfRow($html, __('dashboard.team_unassigned')))
        ->toBe([__('dashboard.team_unassigned'), '1', '0', '0', '1', '0', '0%'])
        ->and(tpfRow($html, __('dashboard.team_total')))
        ->toBe([__('dashboard.team_total'), '5', '2', '1', '1', '1', '40%']);

    // Gamma owns nothing today, so the table does not carry an empty row.
    expect(tpfRow($html, 'Gamma'))->toBe([]);
});

test('the table total is the same order count the KPI reports', function () {
    $f = tpfScenario();

    $html = tpfHtml($f['user'], $f['store']);
    $summary = tpfSummary($f['user'], $f['store']);

    $total = tpfRow($html, __('dashboard.team_total'));

    expect($total)->not->toBe([])
        ->and($total[1])->toBe((string) $summary['total_orders'])
        ->and($summary['total_orders'])->toBe(5);
});

test('the delivery view reads the newest tracking attempt and still counts one order once', function () {
    $f = tpfScenario();

    $html = tpfHtml($f['user'], $f['store'], ['md' => 'delivery']);

    expect($html)->toContain(__('dashboard.team_performance_delivery'));

    // member, assigned, delivered, returned, in progress, delivery rate,
    // return rate, delivered revenue. Alpha is credited with the re-shipped
    // order through its newest attempt only: Beta's older attempt must not
    // hand the same order a second time.
    expect(tpfRow($html, 'Alpha'))->toBe(['Alpha', '2', '1', '0', '1', '50%', '0%', '300.00 DZD'])
        ->and(tpfRow($html, 'Beta'))->toBe(['Beta', '2', '0', '1', '1', '0%', '100%', '0.00 DZD'])
        ->and(tpfRow($html, __('dashboard.team_unassigned')))
        ->toBe([__('dashboard.team_unassigned'), '1', '0', '0', '1', '0%', '0%', '0.00 DZD'])
        ->and(tpfRow($html, __('dashboard.team_total')))
        ->toBe([__('dashboard.team_total'), '5', '1', '1', '3', '20%', '50%', '300.00 DZD']);
});

test('the window decides which orders reach the table', function () {
    $f = tpfScenario();

    $today = tpfHtml($f['user'], $f['store']);
    $yesterday = tpfHtml($f['user'], $f['store'], ['p' => 'yesterday']);

    $todayTotal = tpfRow($today, __('dashboard.team_total'));

    expect(tpfRow($today, 'Gamma'))->toBe([])
        ->and($todayTotal)->not->toBe([])
        ->and($todayTotal[1])->toBe('5')
        ->and(tpfRow($yesterday, 'Gamma'))->toBe(['Gamma', '1', '1', '0', '0', '0', '100%'])
        ->and(tpfRow($yesterday, 'Alpha'))->toBe([])
        ->and(tpfRow($yesterday, __('dashboard.team_total')))->toBe([
            __('dashboard.team_total'), '1', '1', '0', '0', '0', '100%',
        ]);
});

test('a carrier filter narrows the table the same way it narrows the KPIs', function () {
    $f = tpfScenario();

    $html = tpfHtml($f['user'], $f['store'], ['c' => $f['carrierB']->id]);

    $total = tpfRow($html, __('dashboard.team_total'));

    expect(tpfRow($html, 'Beta'))->toBe(['Beta', '2', '0', '1', '0', '1', '0%'])
        ->and(tpfRow($html, 'Alpha'))->toBe([])
        ->and(tpfRow($html, __('dashboard.team_unassigned')))->toBe([])
        ->and($total)->not->toBe([])
        ->and($total[1])->toBe('2');
});

test('picking one member drops the total because that one row already is it', function () {
    $f = tpfScenario();

    $html = tpfHtml($f['user'], $f['store'], ['m' => $f['alpha']->id]);

    expect(tpfRow($html, 'Alpha'))->toBe(['Alpha', '2', '2', '0', '0', '0', '100%'])
        ->and(tpfRow($html, 'Beta'))->toBe([])
        ->and(tpfRow($html, __('dashboard.team_unassigned')))->toBe([])
        ->and(tpfRow($html, __('dashboard.team_total')))->toBe([]);
});

test('a manager without team wide stats sees only their own team and no unassigned row', function () {
    [$user, $store] = tpfStore();

    // StoreRoles grants MANAGER STATS_TEAM_VIEW, which would make this a free
    // choice and the scope null: the stock template and the "manager: self plus
    // subordinates" rule disagree. The permission hub allows dropping exactly
    // this one permission, which is what the restricted branch is for.
    expect(StoreRoles::permissions(StoreRoleEnum::MANAGER))
        ->toContain(StorePermissionEnum::STATS_TEAM_VIEW->value);

    $manager = tpfMembership($store, null, StoreRoleEnum::MANAGER, array_values(array_diff(
        StoreRoles::permissions(StoreRoleEnum::MANAGER),
        [StorePermissionEnum::STATS_TEAM_VIEW->value],
    )));

    $subordinate = tpfMembership($store, null, StoreRoleEnum::STAFF, null, $manager);
    $colleague = tpfMembership($store, null, StoreRoleEnum::STAFF);
    tpfRename($subordinate, 'Subordinate Member');

    tpfOrder($store, ['status_id' => tpfStatus(OrderStatus::CONFIRMED), 'assigned_to_membership_id' => $manager->id]);
    tpfOrder($store, ['status_id' => tpfStatus(OrderStatus::PENDING), 'assigned_to_membership_id' => $subordinate->id]);
    // Outside the manager's team: the scope must remove it from the table.
    tpfOrder($store, ['status_id' => tpfStatus(OrderStatus::CONFIRMED), 'assigned_to_membership_id' => $colleague->id]);
    tpfOrder($store, ['status_id' => tpfStatus(OrderStatus::CANCELED)]);

    $html = tpfHtml($manager->user, $store);

    $total = tpfRow($html, __('dashboard.team_total'));

    expect($total)->not->toBe([])
        ->and($total[1])->toBe('2')
        ->and(tpfRow($html, 'Subordinate Member'))->not->toBe([])
        ->and(tpfRow($html, __('dashboard.team_unassigned')))->toBe([]);
});

test('an order owned by another store is never named in the table', function () {
    [$user, $store] = tpfStore();

    $mine = tpfMembership($store, null, StoreRoleEnum::STAFF);

    $foreignStore = Store::create([
        'user_id' => $user->id,
        'name' => 'Foreign Store',
        'slug' => 'team-performance-foreign-'.uniqid(),
        'status' => 'active',
    ]);
    $foreign = tpfMembership($foreignStore, null, StoreRoleEnum::STAFF);
    tpfRename($foreign, 'Foreign Member');

    tpfOrder($store, ['status_id' => tpfStatus(OrderStatus::CONFIRMED), 'assigned_to_membership_id' => $mine->id]);
    // Data that should not exist: an order of ours assigned to their member.
    tpfOrder($store, ['status_id' => tpfStatus(OrderStatus::CONFIRMED), 'assigned_to_membership_id' => $foreign->id]);

    $html = tpfHtml($user, $store);

    $total = tpfRow($html, __('dashboard.team_total'));

    // The foreign membership is dropped rather than named, so the table totals
    // one order fewer than the KPI, which counts every order of the store. The
    // mismatch is deliberate: naming it would leak a membership this viewer
    // cannot see, and the row would have nowhere to sit in the member list.
    expect(tpfRow($html, 'Foreign Member'))->toBe([])
        ->and($total)->not->toBe([])
        ->and($total[1])->toBe('1')
        ->and(tpfSummary($user, $store)['total_orders'])->toBe(2);
});

test('a locked member gets no table at all', function () {
    [$user, $store] = tpfStore();

    $staff = tpfMembership($store, null, StoreRoleEnum::STAFF);

    tpfOrder($store, ['status_id' => tpfStatus(OrderStatus::CONFIRMED), 'assigned_to_membership_id' => $staff->id]);

    $html = tpfHtml($staff->user, $store);

    expect($html)
        ->not->toContain('wire:key="dash-team-')
        ->not->toContain(__('dashboard.team_performance_title'));
});

test('an owner with no orders in the window gets the empty state', function () {
    [$user, $store] = tpfStore();

    $html = tpfHtml($user, $store);

    expect($html)->toContain(__('dashboard.team_no_activity'));
});

test('the table is wired into the dashboard for both views', function () {
    $f = tpfScenario();

    $this->actingAs($f['user'])->withSession(['current_store_id' => $f['store']->id]);

    Volt::test('merchant.dashboard')
        ->assertSee(__('dashboard.team_performance_title'));

    expect(tpfHtml($f['user'], $f['store']))
        ->toContain(__('dashboard.team_performance_confirmation'))
        ->and(tpfHtml($f['user'], $f['store'], ['md' => 'delivery']))
        ->toContain(__('dashboard.team_performance_delivery'));
});
