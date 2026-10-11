<?php

use App\Domains\Analytics\DTOs\DashboardFilter;
use App\Domains\Analytics\Support\ConfirmationRateDenominator;
use App\Domains\Analytics\Support\ConfirmationStatsGroups;
use App\Domains\Analytics\Support\ConfirmationStatsQuery;
use App\Domains\Analytics\Support\ConfirmationStatsReport;
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

function csqUser(): User
{
    return roleUser('merchant');
}

function csqStore(): Store
{
    $user = csqUser();

    return Store::create([
        'user_id' => $user->id,
        'name' => 'Confirmation Store',
        'slug' => 'confirmation-store-'.uniqid(),
        'status' => 'active',
    ]);
}

function csqMembership(Store $store, User $user, StoreRoleEnum $role = StoreRoleEnum::STAFF): StoreMembership
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

function csqStatusId(string $key): string
{
    return DB::table('statuses')
        ->where('type', 'order')
        ->where('key', $key)
        ->value('id');
}

function csqOrder(Store $store, ?string $membershipId, string $statusKey, string $number): Order
{
    return Order::create([
        'store_id' => $store->id,
        'number' => $number,
        'total_amount' => 500,
        'status_id' => csqStatusId($statusKey),
        'delivery_type' => 'home',
        'assigned_to_membership_id' => $membershipId,
    ]);
}

function csqFilter(Store $store, array $overrides = []): DashboardFilter
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

function csqRows(DashboardFilter $filter): array
{
    $rows = app(ConfirmationStatsQuery::class)->run($filter);
    $keyed = [];

    foreach ($rows as $row) {
        $keyed[$row['membership_id'].'|'.$row['status_key']] = $row;
    }

    return $keyed;
}

test('counts live and trashed orders per member and status in one read', function () {
    $store = csqStore();
    $memberA = csqMembership($store, csqUser());
    $memberB = csqMembership($store, csqUser());

    csqOrder($store, $memberA->id, 'confirmed', 'CSQ-A-1');
    csqOrder($store, $memberA->id, 'confirmed', 'CSQ-A-2');
    csqOrder($store, $memberA->id, 'confirmed', 'CSQ-A-3');
    csqOrder($store, $memberA->id, 'pending', 'CSQ-A-4');
    csqOrder($store, $memberB->id, 'no_answer_1', 'CSQ-B-1');
    csqOrder($store, $memberB->id, 'no_answer_1', 'CSQ-B-2');

    csqOrder($store, $memberA->id, 'confirmed', 'CSQ-A-TRASHED')->delete();

    $rows = csqRows(csqFilter($store));

    expect($rows[$memberA->id.'|confirmed'])->toMatchArray(['total' => 3, 'deleted' => 1])
        ->and($rows[$memberA->id.'|pending'])->toMatchArray(['total' => 1, 'deleted' => 0])
        ->and($rows[$memberB->id.'|no_answer_1'])->toMatchArray(['total' => 2, 'deleted' => 0])
        ->and($rows)->toHaveCount(3);
});

test('an order whose status moved inside the window is counted on the last-status axis', function () {
    $store = csqStore();
    $member = csqMembership($store, csqUser());

    $order = csqOrder($store, $member->id, 'confirmed', 'CSQ-AXIS-1');
    $order->forceFill([
        'created_at' => now()->subDays(10),
        'last_status_changed_at' => now(),
    ])->save();

    $today = now()->startOfDay();
    $window = [
        'period' => 'custom',
        'from' => CarbonImmutable::parse($today)->utc(),
        'to' => CarbonImmutable::parse(now()->endOfDay())->utc(),
    ];

    $byCreation = csqRows(csqFilter($store, $window));
    expect($byCreation)->toHaveCount(0);

    $byStatus = csqRows(csqFilter($store, $window + ['dateBasis' => DashboardFilter::DATE_BASIS_LAST_STATUS]));
    expect($byStatus[$member->id.'|confirmed'])->toMatchArray(['total' => 1, 'deleted' => 0]);
});

test('the report folds the query rows so the card, a member row and the total agree', function () {
    $store = csqStore();
    $memberA = csqMembership($store, csqUser());
    $memberB = csqMembership($store, csqUser());

    csqOrder($store, $memberA->id, 'confirmed', 'CRR-A-1');
    csqOrder($store, $memberA->id, 'confirmed', 'CRR-A-2');
    csqOrder($store, $memberA->id, 'confirmed', 'CRR-A-3');
    csqOrder($store, $memberA->id, 'canceled', 'CRR-A-4');

    csqOrder($store, $memberB->id, 'confirmed', 'CRR-B-1');
    csqOrder($store, $memberB->id, 'no_answer_1', 'CRR-B-2');

    // A trashed confirmation stays in the bracket but must never move the rate.
    csqOrder($store, $memberA->id, 'confirmed', 'CRR-A-TRASH')->delete();

    $rows = app(ConfirmationStatsQuery::class)->run(csqFilter($store));
    $report = new ConfirmationStatsReport(ConfirmationRateDenominator::resolve(null));

    $card = $report->build($rows);
    $members = $report->perMember($rows);

    expect($card['confirmed'])->toBe(4)
        ->and($card['denominator'])->toBe(6)
        ->and($card['rate'])->toBe(67)
        ->and($card['groups'][ConfirmationStatsGroups::CONFIRMED]['deleted'])->toBe(1)
        ->and($members[$memberA->id]['rate'])->toBe(75)
        ->and($members[$memberB->id]['rate'])->toBe(50)
        ->and($card['confirmed'])->toBe(array_sum(array_column($members, 'confirmed')))
        ->and($card['denominator'])->toBe(array_sum(array_column($members, 'denominator')));
});
