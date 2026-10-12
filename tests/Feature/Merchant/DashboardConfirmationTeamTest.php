<?php

use App\Domains\Analytics\DTOs\DashboardFilter;
use App\Domains\Analytics\Support\OrderStatusIdMap;
use App\Enums\Store\OrderStatus;
use App\Enums\Store\StorePermissionEnum;
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
use Livewire\Volt\Volt;
use Spatie\Permission\Models\Role;

/*
|--------------------------------------------------------------------------
| PHASE 39-M5 - confirmation team section
|--------------------------------------------------------------------------
|
| The confirmation view's team section follows the base tab (D5): the count
| tab shows each member's orders and trash, the rate tab the group summary and
| the rate bar. Deleted orders are always shown as a trash badge and never
| enter the rate (D7); the without/with-trash toggle only decides which members
| are named. The delivery view keeps its own 37-J section (now cards).
|
*/

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(StoreRolesAndPermissionsSeeder::class);
    $this->seed(SystemStatusesSeeder::class);
    $this->seed(PlansSeeder::class);

    // Algiers is UTC+1, so "today" below is a fixed window.
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-03-10 12:00:00', 'UTC'));
});

afterEach(function () {
    CarbonImmutable::setTestNow();
});

function ctfStore(): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate(StoreRoleEnum::OWNER->value, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Confirmation Team Store',
        'slug' => 'confirmation-team-'.uniqid(),
        'status' => 'active',
    ]);

    ctfMembership($store, $user, StoreRoleEnum::OWNER);

    return [$user, $store];
}

function ctfMembership(Store $store, ?User $user, StoreRoleEnum $role, ?array $permissions = null): StoreMembership
{
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

function ctfStatus(OrderStatus $status): string
{
    return app(OrderStatusIdMap::class)->id($status);
}

function ctfOrder(Store $store, OrderStatus $status, array $attributes = []): Order
{
    $order = Order::query()->create(array_merge([
        'store_id' => $store->id,
        'status_id' => ctfStatus($status),
    ], $attributes));

    $order->forceFill(['created_at' => CarbonImmutable::parse('2026-03-10 08:00:00', 'UTC')])->save();

    return $order;
}

function ctfRename(StoreMembership $membership, string $name): void
{
    User::query()->whereKey($membership->user_id)->update(['name' => $name]);
}

function ctfHtml(User $user, Store $store, array $query = []): string
{
    return test()->actingAs($user)
        ->withSession(['current_store_id' => $store->id])
        ->get(route('merchant.dashboard', $query + ['store' => $store->slug]))
        ->assertOk()
        ->getContent();
}

/** Flattened text of the confirmation team section, or '' when absent. */
function ctfSection(string $html): string
{
    $start = strpos($html, 'wire:key="dash-conf-team-');

    if ($start === false) {
        return '';
    }

    $section = substr($html, $start);
    // The team card is the last section before the tables grid.
    $end = strpos($section, 'class="edz-stagger');

    return $end === false ? $section : substr($section, 0, $end);
}

/** Flattened text of the confirmation team card holding $name, or ''. */
function ctfCard(string $html, string $name): string
{
    $section = ctfSection($html);

    if ($section === '') {
        return '';
    }

    foreach (preg_split('/class="rounded-xl border border-surface-border/', $section) as $chunk) {
        $text = trim(preg_replace('/\s+/', ' ', strip_tags($chunk)));

        if (str_contains($text, $name)) {
            return $text;
        }
    }

    return '';
}

/**
 * Alpha owns three live orders today — one confirmed, one cancelled and one
 * delivered (outside the rate) — plus one trashed confirmed order. The pending
 * unowned order is the unattributed bucket.
 */
function ctfScenario(): array
{
    [$user, $store] = ctfStore();

    $alpha = ctfMembership($store, null, StoreRoleEnum::STAFF);
    ctfRename($alpha, 'Alpha');

    ctfOrder($store, OrderStatus::CONFIRMED, [
        'assigned_to_membership_id' => $alpha->id,
        'confirmed_by_membership_id' => $alpha->id,
    ]);
    ctfOrder($store, OrderStatus::CANCELED, [
        'assigned_to_membership_id' => $alpha->id,
        'confirmed_by_membership_id' => $alpha->id,
    ]);
    ctfOrder($store, OrderStatus::DELIVERED, [
        'assigned_to_membership_id' => $alpha->id,
        'confirmed_by_membership_id' => $alpha->id,
    ]);

    $trashed = ctfOrder($store, OrderStatus::CONFIRMED, [
        'assigned_to_membership_id' => $alpha->id,
        'confirmed_by_membership_id' => $alpha->id,
    ]);
    $trashed->delete();

    ctfOrder($store, OrderStatus::PENDING);

    return ['user' => $user, 'store' => $store, 'alpha' => $alpha];
}

test('the count tab lists each member with all orders and the trash count', function () {
    $f = ctfScenario();

    $html = ctfHtml($f['user'], $f['store']);

    expect($html)
        ->toContain(__('dashboard.team_performance_title'))
        ->toContain(__('dashboard.team_performance_confirmation'));

    $alpha = ctfCard($html, 'Alpha');

    // Three live orders; the trashed one sits in its own (n) trash badge.
    expect($alpha)->toContain(__('dashboard.team_all_orders').' 3')
        ->and($alpha)->toContain(__('dashboard.deleted_brackets', ['count' => 1]));

    expect(ctfCard($html, __('dashboard.team_unattributed')))->toContain(__('dashboard.team_all_orders').' 1');

    // The trailing total row is answered by the member cards and is never
    // rendered as a card of its own.
    expect(ctfSection($html))->not->toContain(__('dashboard.team_total'));
});

test('the rate tab follows the base tab and shows the rate plus its group breakdown', function () {
    $f = ctfScenario();

    $html = ctfHtml($f['user'], $f['store'], ['ct' => 'rate']);

    $alpha = ctfCard($html, 'Alpha');

    // D12: the delivered order counts as confirmed and stays inside the rate,
    // so the rate is confirmed+delivered over confirmed+delivered+canceled =
    // 2 / 3 = 67%. The rate tab lists each group, passed-to-delivery included.
    expect($alpha)->toContain('67')
        ->and($alpha)->toContain('2 / 3')
        ->and($alpha)->toContain(__('dashboard.group_confirmed').' 1')
        ->and($alpha)->toContain(__('dashboard.group_canceled').' 1')
        ->and($alpha)->toContain(__('dashboard.group_pending').' 0')
        ->and($alpha)->toContain(__('dashboard.group_passed_to_delivery').' 1');
});

test('deleted orders never enter the rate but always show as a trash badge', function () {
    $f = ctfScenario();

    $rate = ctfCard(ctfHtml($f['user'], $f['store'], ['ct' => 'rate']), 'Alpha');

    // The trashed order is confirmed, so the confirmed chip still shows 1 and
    // carries the trash badge (the deleted_brackets tooltip) while the rate
    // still reads 2 / 3 — the deleted order is outside it (D7).
    expect($rate)->toContain(__('dashboard.group_confirmed').' 1')
        ->and($rate)->toContain(__('dashboard.group_deleted_hint', ['count' => 1, 'group' => __('dashboard.group_confirmed')]))
        ->and($rate)->toContain('2 / 3');
});

test('the without trash toggle hides a soft-deleted member, the with trash toggle names it', function () {
    $f = ctfScenario();

    $f['alpha']->delete();

    $without = ctfHtml($f['user'], $f['store']);
    $with = ctfHtml($f['user'], $f['store'], ['tt' => 1]);

    expect(ctfCard($without, 'Alpha'))->toBe('')
        ->and(ctfCard($with, 'Alpha'))->toContain('Alpha')
        ->and(ctfCard($with, 'Alpha'))->toContain('('.__('dashboard.team_member_deleted').')');
});

test('picking one member drops the total row', function () {
    $f = ctfScenario();

    $html = ctfHtml($f['user'], $f['store'], ['m' => $f['alpha']->id]);

    expect(ctfCard($html, 'Alpha'))->toContain(__('dashboard.team_all_orders').' 3')
        ->and(ctfCard($html, __('dashboard.team_unattributed')))->toBe('')
        ->and(ctfSection($html))->not->toContain(__('dashboard.team_total'));
});

test('the unattributed sentinel shows only the unattributed row', function () {
    $f = ctfScenario();

    $html = ctfHtml($f['user'], $f['store'], ['m' => DashboardFilter::UNATTRIBUTED]);

    expect(ctfCard($html, 'Alpha'))->toBe('')
        ->and(ctfCard($html, __('dashboard.team_unattributed')))->toContain(__('dashboard.team_all_orders').' 1')
        ->and(ctfSection($html))->not->toContain(__('dashboard.team_total'));
});

test('a staff member without team stats gets no confirmation team section', function () {
    [$user, $store] = ctfStore();

    $staff = ctfMembership($store, null, StoreRoleEnum::STAFF, [
        StorePermissionEnum::ORDER_VIEW->value,
    ]);

    ctfOrder($store, OrderStatus::CONFIRMED, [
        'assigned_to_membership_id' => $staff->id,
        'confirmed_by_membership_id' => $staff->id,
    ]);

    $html = ctfHtml($staff->user, $store);

    expect(ctfSection($html))->toBe('')
        ->and($html)->not->toContain(__('dashboard.team_performance_title'));
});

test('an owner with no orders in the window gets the empty state', function () {
    [$user, $store] = ctfStore();

    expect(ctfHtml($user, $store))->toContain(__('dashboard.team_no_activity'));
});

test('the section is wired into the confirmation view and the delivery view keeps its own', function () {
    $f = ctfScenario();

    $this->actingAs($f['user'])->withSession(['current_store_id' => $f['store']->id]);

    Volt::test('merchant.dashboard')->assertSee(__('dashboard.team_performance_title'));

    expect(ctfSection(ctfHtml($f['user'], $f['store'])))->not->toBe('')
        ->and(ctfHtml($f['user'], $f['store'], ['md' => 'delivery']))
        ->toContain(__('dashboard.team_performance_delivery'));
});
