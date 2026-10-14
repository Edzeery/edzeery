<?php

use App\Domains\Analytics\Support\OrderStatusIdMap;
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
| PHASE 37-J - team performance table (delivery view)
|--------------------------------------------------------------------------
|
| Since Phase 39-M5 the confirmation view carries its own tab-following member
| section (see DashboardConfirmationTeamTest); the 37-J table now serves the
| delivery view alone. These tests drive the rendered page: the workload walk,
| the credit rule, and the scoping the permission model produces.
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
): StoreMembership {
    $user ??= roleUser('merchant');
    $user->assignRole(Role::findOrCreate($role->value, 'merchant'));

    $membership = StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'invited_by' => $store->user_id,
        'is_active' => true,
        'role' => $role->value,
    ]);

    $membership->syncPermissions($permissions ?? StoreRoles::permissions($role));

    return $membership;
}

function tpfStatus(OrderStatus $status): string
{
    return app(OrderStatusIdMap::class)->id($status);
}

function tpfOrder(Store $store, array $attributes = []): Order
{
    $order = Order::query()->create(array_merge([
        'store_id' => $store->id,
        'status_id' => tpfStatus(OrderStatus::PENDING),
    ], $attributes));

    $order->forceFill(['created_at' => CarbonImmutable::parse('2026-03-10 08:00:00', 'UTC')])->save();

    return $order;
}

/** A tracking attempt; its ULID orders the re-ship walk (newest wins). */
function tpfTracking(Store $store, Order $order, StoreMembership $member, string $id): OrderTracking
{
    $tracking = new OrderTracking([
        'store_id' => $store->id,
        'order_id' => $order->id,
        'assigned_to_membership_id' => $member->id,
    ]);

    $tracking->id = $id;
    $tracking->save();

    return $tracking;
}

function tpfHtml(User $user, Store $store, array $query = []): string
{
    return test()->actingAs($user)
        ->withSession(['current_store_id' => $store->id])
        ->get(route('merchant.dashboard', $query + ['store' => $store->slug, 'md' => 'delivery']))
        ->assertOk()
        ->getContent();
}

/** The values of the delivery card holding $name, or [] when absent. */
function tpfRow(string $html, string $name): array
{
    // Each delivery card opens with the same root classes, so the split gives
    // one chunk per member, the total and the unattributed bucket.
    foreach (preg_split('/class="flex flex-col gap-4 rounded-xl border p-4/', $html) as $card) {
        $text = trim(preg_replace('/\s+/', ' ', strip_tags($card)));

        if (! str_contains($text, $name)) {
            continue;
        }

        $count = fn (string $label): string => preg_match('/'.preg_quote($label, '/').'\s+(\d+)/', $text, $m) ? $m[1] : '0';
        $rate = fn (string $label): string => preg_match('/'.preg_quote($label, '/').'\s+(\d+)%/', $text, $m) ? $m[1].'%' : '0%';

        preg_match('/'.preg_quote(__('dashboard.col_delivered_revenue'), '/').':\s*([\d.,]+ ?\S+)/', $text, $revenue);

        return [
            $name,
            $count(__('dashboard.col_assigned')),
            $count(__('dashboard.col_delivered')),
            $count(__('dashboard.col_returned')),
            $count(__('dashboard.col_in_progress')),
            $rate(__('dashboard.col_delivery_rate')),
            $rate(__('dashboard.col_return_rate')),
            $revenue[1] ?? '0.00 DZD',
        ];
    }

    return [];
}

/** Memberships carry no display name column; the viewer's user owns it. */
function tpfRename(StoreMembership $membership, string $name): void
{
    User::query()->whereKey($membership->user_id)->update(['name' => $name]);
}

test('the delivery view counts workload and credit by the newest tracking member', function () {
    [$user, $store] = tpfStore();

    $alpha = tpfMembership($store, null, StoreRoleEnum::STAFF);
    $beta = tpfMembership($store, null, StoreRoleEnum::STAFF);
    tpfRename($alpha, 'Alpha');
    tpfRename($beta, 'Beta');

    $alphaConfirmed = tpfOrder($store, ['status_id' => tpfStatus(OrderStatus::CONFIRMED), 'assigned_to_membership_id' => $alpha->id, 'confirmed_by_membership_id' => $alpha->id]);
    $alphaDelivered = tpfOrder($store, ['status_id' => tpfStatus(OrderStatus::DELIVERED), 'total_amount' => 300, 'assigned_to_membership_id' => $alpha->id, 'confirmed_by_membership_id' => $alpha->id]);
    $betaReturned = tpfOrder($store, ['status_id' => tpfStatus(OrderStatus::RETURNED), 'total_amount' => 60, 'assigned_to_membership_id' => $beta->id, 'confirmed_by_membership_id' => $beta->id]);

    tpfTracking($store, $alphaConfirmed, $alpha, '01J000000000000000000001AA');
    // Re-shipped: the older attempt went to Beta, the newest is Alpha's.
    tpfTracking($store, $alphaDelivered, $beta, '01J000000000000000000001B1');
    tpfTracking($store, $alphaDelivered, $alpha, '01J000000000000000000001B2');
    tpfTracking($store, $betaReturned, $beta, '01J000000000000000000001CC');

    $html = tpfHtml($user, $store);

    expect($html)->toContain(__('dashboard.team_performance_delivery'));

    // Alpha: 2 assigned, 1 delivered, 1 in progress, delivery rate 1/2 = 50%.
    expect(tpfRow($html, 'Alpha'))->toBe(['Alpha', '2', '1', '0', '1', '50%', '0%', '300.00 DZD'])
        // Beta keeps only the returned order's workload: the re-shipped order's
        // newest attempt is Alpha's, so return rate is 1/1 = 100%.
        ->and(tpfRow($html, 'Beta'))->toBe(['Beta', '1', '0', '1', '0', '0%', '100%', '0.00 DZD'])
        ->and(tpfRow($html, __('dashboard.team_total')))->not->toBe([]);
});

test('the delivery view credits the member whose tracking shipped the order', function () {
    [$user, $store] = tpfStore();

    $alpha = tpfMembership($store, null, StoreRoleEnum::STAFF);
    $gamma = tpfMembership($store, null, StoreRoleEnum::STAFF);
    tpfRename($alpha, 'Alpha');
    tpfRename($gamma, 'Gamma');

    $alphaConfirmed = tpfOrder($store, ['status_id' => tpfStatus(OrderStatus::CONFIRMED), 'assigned_to_membership_id' => $alpha->id, 'confirmed_by_membership_id' => $alpha->id]);
    $alphaDelivered = tpfOrder($store, ['status_id' => tpfStatus(OrderStatus::DELIVERED), 'total_amount' => 300, 'assigned_to_membership_id' => $alpha->id, 'confirmed_by_membership_id' => $alpha->id]);
    // Confirmed by Alpha but shipped through Gamma's port: PHASE 37-N9 gives the
    // delivery credit (delivered, money) to the shipper whose newest tracking
    // row carried it, so Gamma owns this one and Alpha never sees it.
    $pivot = tpfOrder($store, ['status_id' => tpfStatus(OrderStatus::DELIVERED), 'total_amount' => 700, 'confirmed_by_membership_id' => $alpha->id]);

    tpfTracking($store, $alphaConfirmed, $alpha, '01J000000000000000000002AA');
    tpfTracking($store, $alphaDelivered, $alpha, '01J000000000000000000002BB');
    tpfTracking($store, $pivot, $gamma, '01J000000000000000000002CC');

    $html = tpfHtml($user, $store);

    // Alpha shipped two, delivered one: 1/2 = 50%.
    expect(tpfRow($html, 'Alpha'))->toBe(['Alpha', '2', '1', '0', '1', '50%', '0%', '300.00 DZD'])
        // Gamma shipped one and delivered it: 1/1 = 100%.
        ->and(tpfRow($html, 'Gamma'))->toBe(['Gamma', '1', '1', '0', '0', '100%', '0%', '700.00 DZD']);
});

test('a tracking-only member is named on the delivery view', function () {
    [$user, $store] = tpfStore();

    $tracker = tpfMembership($store, null, StoreRoleEnum::STAFF, [
        StorePermissionEnum::ORDER_VIEW->value,
        StorePermissionEnum::CRM_ORDER_TRACKING->value,
    ]);
    tpfRename($tracker, 'Tracker Only');

    $order = tpfOrder($store, ['status_id' => tpfStatus(OrderStatus::CONFIRMED)]);
    tpfTracking($store, $order, $tracker, '01J000000000000000000003AA');

    expect(tpfRow(tpfHtml($user, $store), 'Tracker Only'))->not->toBe([]);
});

test('a member without delivery stats gets no delivery table', function () {
    [$user, $store] = tpfStore();

    $staff = tpfMembership($store, null, StoreRoleEnum::STAFF, [
        StorePermissionEnum::ORDER_VIEW->value,
    ]);

    tpfOrder($store, ['status_id' => tpfStatus(OrderStatus::CONFIRMED), 'assigned_to_membership_id' => $staff->id]);

    $html = tpfHtml($staff->user, $store);

    expect($html)
        ->not->toContain('wire:key="dash-team-')
        ->not->toContain(__('dashboard.team_performance_title'));
});

test('the delivery table is wired into the dashboard', function () {
    [$user, $store] = tpfStore();

    $member = tpfMembership($store, null, StoreRoleEnum::STAFF);
    $order = tpfOrder($store, ['status_id' => tpfStatus(OrderStatus::CONFIRMED), 'assigned_to_membership_id' => $member->id]);
    tpfTracking($store, $order, $member, '01J000000000000000000003AA');

    $this->actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.dashboard')
        ->assertSee(__('dashboard.team_performance_title'));

    expect(tpfHtml($user, $store))
        ->toContain('wire:key="dash-team-')
        ->and(tpfHtml($user, $store))
        ->toContain(__('dashboard.team_performance_delivery'));
});
