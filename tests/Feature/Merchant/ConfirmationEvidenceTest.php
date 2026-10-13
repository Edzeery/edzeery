<?php

use App\Domains\Analytics\DTOs\DashboardFilter;
use App\Domains\Analytics\Support\ConfirmationRate;
use App\Domains\Analytics\Support\ConfirmationStatsGroups;
use App\Domains\Analytics\Support\ConfirmationStatsQuery;
use App\Domains\Analytics\Support\ConfirmationStatsReport;
use App\Domains\Analytics\Support\DashboardSummaryQuery;
use App\Domains\Analytics\Support\DashboardTrendQuery;
use App\Domains\Analytics\Support\OrderStatusIdMap;
use App\Enums\Store\StoreRoleEnum;
use App\Models\Orders\Order;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Models\User;
use App\Support\StoreRoles;
use Carbon\CarbonImmutable;
use Database\Seeders\PlansSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\StoreRolesAndPermissionsSeeder;
use Database\Seeders\SystemStatusesSeeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(StoreRolesAndPermissionsSeeder::class);
    $this->seed(SystemStatusesSeeder::class);
    $this->seed(PlansSeeder::class);
});

function cevUser(): User
{
    return roleUser('merchant');
}

function cevStore(): Store
{
    $user = cevUser();

    return Store::create([
        'user_id' => $user->id,
        'name' => 'Evidence Store',
        'slug' => 'evidence-store-'.uniqid(),
        'status' => 'active',
    ]);
}

function cevMembership(Store $store, User $user, StoreRoleEnum $role = StoreRoleEnum::STAFF): StoreMembership
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

function cevStatusId(string $key): string
{
    return DB::table('statuses')
        ->where('type', 'order')
        ->where('key', $key)
        ->value('id');
}

function cevOrder(Store $store, ?string $membershipId, string $statusKey, string $number): Order
{
    return Order::create([
        'store_id' => $store->id,
        'number' => $number,
        'total_amount' => 500,
        'status_id' => cevStatusId($statusKey),
        'delivery_type' => 'home',
        'assigned_to_membership_id' => $membershipId,
    ]);
}

function cevFilter(Store $store, array $overrides = []): DashboardFilter
{
    return new DashboardFilter(
        period: $overrides['period'] ?? 'all',
        from: $overrides['from'] ?? null,
        to: $overrides['to'] ?? null,
        timezone: 'UTC',
        utcOffsetSeconds: 0,
        storeId: $store->id,
        dateBasis: $overrides['dateBasis'] ?? DashboardFilter::DATE_BASIS_CREATED,
    );
}

function cevGroups(Store $store): array
{
    $rows = app(ConfirmationStatsQuery::class)->run(cevFilter($store));

    return (new ConfirmationStatsReport(ConfirmationRate::GROSS, []))->build($rows)['groups'];
}

test('an order confirmed then cancelled stays in the confirmed bucket', function () {
    $store = cevStore();
    $member = cevMembership($store, cevUser());

    $order = cevOrder($store, $member->id, 'canceled', 'CEV-1');
    $order->forceFill(['confirmed_at' => now()->subHour(), 'confirmed_by_membership_id' => $member->id])->save();

    $groups = cevGroups($store);

    expect($groups[ConfirmationStatsGroups::CONFIRMED]['total'])->toBe(1)
        ->and($groups[ConfirmationStatsGroups::CANCELED]['total'])->toBe(0);
});

test('a wide confirmed-group status without a stamp still counts as confirmed', function () {
    $store = cevStore();
    $member = cevMembership($store, cevUser());

    $order = cevOrder($store, $member->id, 'returned', 'CEV-2');

    expect($order->confirmed_at)->toBeNull();

    $groups = cevGroups($store);

    expect($groups[ConfirmationStatsGroups::CONFIRMED]['total'])->toBe(1)
        ->and($groups[ConfirmationStatsGroups::CANCELED]['total'])->toBe(0);
});

test('a never-confirmed canceled order stays in the canceled bucket', function () {
    $store = cevStore();
    $member = cevMembership($store, cevUser());

    cevOrder($store, $member->id, 'canceled', 'CEV-3');

    $groups = cevGroups($store);

    expect($groups[ConfirmationStatsGroups::CANCELED]['total'])->toBe(1)
        ->and($groups[ConfirmationStatsGroups::CONFIRMED]['total'])->toBe(0);
});

test('the confirmation trend counts an ever-confirmed order on its own band only', function () {
    $store = cevStore();
    $member = cevMembership($store, cevUser());

    $order = cevOrder($store, $member->id, 'canceled', 'CEV-4');
    $order->forceFill(['confirmed_at' => now()->subHour()])->save();

    $map = app(OrderStatusIdMap::class);
    $trend = app(DashboardTrendQuery::class)->run(cevFilter($store), $map->resolver(), fn (array $s) => $map->ids($s));
    $byKey = collect($trend['series'])->keyBy('key');

    expect(array_sum($byKey['confirmed']['values']))->toBe(1)
        ->and(array_sum($byKey['canceled']['values']))->toBe(0);
});

test('the summary excludes an ever-confirmed order from pending and canceled', function () {
    $store = cevStore();
    $member = cevMembership($store, cevUser());

    $confirmedThenCanceled = cevOrder($store, $member->id, 'canceled', 'CEV-5');
    $confirmedThenCanceled->forceFill(['confirmed_at' => now()->subHour()])->save();

    $confirmedThenPending = cevOrder($store, $member->id, 'pending', 'CEV-6');
    $confirmedThenPending->forceFill(['confirmed_at' => now()->subHour()])->save();

    cevOrder($store, $member->id, 'pending', 'CEV-7');
    cevOrder($store, $member->id, 'canceled', 'CEV-8');

    $base = Order::query()->where('store_id', $store->id)->whereNull('deleted_at');
    $map = app(OrderStatusIdMap::class);

    $summary = app(DashboardSummaryQuery::class)->run(
        $base,
        cevFilter($store),
        $map->resolver(),
        fn (array $statuses) => $map->ids($statuses),
    );

    expect($summary['pending_count'])->toBe(1)
        ->and($summary['canceled_count'])->toBe(1);
});

test('the last-status basis reads the confirmation axis, not the creation date', function () {
    $store = cevStore();
    $member = cevMembership($store, cevUser());

    $moved = cevOrder($store, $member->id, 'canceled', 'CEV-5');
    $moved->forceFill([
        'created_at' => now()->subDays(10),
        'confirmation_status_changed_at' => now(),
    ])->save();

    $today = now()->startOfDay();
    $window = [
        'period' => 'custom',
        'from' => CarbonImmutable::parse($today)->utc(),
        'to' => CarbonImmutable::parse(now()->endOfDay())->utc(),
    ];

    $byCreation = app(ConfirmationStatsQuery::class)->run(cevFilter($store, $window));
    expect($byCreation)->toHaveCount(0);

    $byStatus = app(ConfirmationStatsQuery::class)->run(
        cevFilter($store, $window + ['dateBasis' => DashboardFilter::DATE_BASIS_LAST_STATUS])
    );
    $groups = (new ConfirmationStatsReport(ConfirmationRate::GROSS, []))->build($byStatus)['groups'];

    expect($groups[ConfirmationStatsGroups::CANCELED]['total'])->toBe(1);
});
