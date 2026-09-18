<?php

use App\Enums\Store\OrderStatus;
use App\Enums\Store\OrderTrackingStatus;
use App\Enums\Store\StorePermissionEnum;
use App\Enums\Store\StoreRoleEnum;
use App\Models\Customer;
use App\Models\Orders\Order;
use App\Models\Orders\OrderTracking;
use App\Models\Status;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Volt\Volt;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\StoreRolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\SystemStatusesSeeder::class);
});

function dqUser(): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate(StoreRoleEnum::OWNER->value, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Queue Store',
        'slug' => 'queue-'.uniqid(),
        'status' => 'active',
    ]);

    $membership = StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'invited_by' => $user->id,
        'is_active' => true,
        'role' => StoreRoleEnum::OWNER->value,
    ]);

    return [$user, $store, $membership];
}

function dqMember(Store $store, array $permissions): StoreMembership
{
    $user = User::factory()->create();

    $membership = StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'invited_by' => $store->user_id,
        'is_active' => true,
        'role' => 'staff',
    ]);

    $membership->syncPermissions($permissions);

    return $membership;
}

function dqOrder(Store $store, string $statusKey, ?StoreMembership $assignedTo = null, bool $overCapacity = false, ?Carbon $createdAt = null): Order
{
    $customer = Customer::create([
        'store_id' => $store->id,
        'name' => 'Queue Customer '.uniqid(),
        'phone' => '0553'.fake()->unique()->numerify('######'),
        'status' => true,
    ]);

    $status = Status::system()
        ->forType('order')
        ->where('key', $statusKey)
        ->firstOrFail();

    return Order::create([
        'store_id' => $store->id,
        'customer_id' => $customer->id,
        'status_id' => $status->id,
        'number' => (new Order(['store_id' => $store->id]))->nextOrderNumber(),
        'total_amount' => 1000,
        'shipping_cost' => 0,
        'assigned_to_membership_id' => $assignedTo?->id,
        'assigned_at' => $assignedTo ? now() : null,
        'assignment_method' => $assignedTo ? 'automatic' : null,
        'over_capacity' => $overCapacity,
        'created_at' => $createdAt ?? now(),
        'updated_at' => $createdAt ?? now(),
    ]);
}

function dqTracking(Order $order, string $trackingStatus, ?StoreMembership $assignedTo = null, bool $overCapacity = false): OrderTracking
{
    return OrderTracking::create([
        'store_id' => $order->store_id,
        'order_id' => $order->id,
        'shipping_provider_id' => null,
        'tracking_number' => 'DQTRK'.uniqid(),
        'tracking_status' => $trackingStatus,
        'assigned_to_membership_id' => $assignedTo?->id,
        'assigned_at' => $assignedTo ? now() : null,
        'assignment_method' => $assignedTo ? 'automatic' : null,
        'over_capacity' => $overCapacity,
        'shipped_at' => now()->subDay(),
        'created_at' => now()->subDay(),
        'updated_at' => now()->subDay(),
    ]);
}

function dqToastIcon(array $params): ?string
{
    $payload = ($params[0] ?? null) && is_array($params[0]) ? $params[0] : $params;

    return $payload['icon'] ?? null;
}

test('the queue page is guarded by ORDER_MANAGE', function () {
    [$user, $store, $membership] = dqUser();
    $member = dqMember($store, [StorePermissionEnum::ORDER_VIEW->value]);

    actingAs($user)->withSession(['current_store_id' => $store->id])
        ->get(route('merchant.order-distribution-queue', $store->slug))
        ->assertOk();

    actingAs($member->user)->withSession(['current_store_id' => $store->id])
        ->get(route('merchant.order-distribution-queue', $store->slug))
        ->assertForbidden();
});

test('the confirmation tab includes unassigned and over-capacity orders, excludes the rest, oldest unassigned first', function () {
    [$user, $store] = dqUser();
    $assignee = dqMember($store, [StorePermissionEnum::ORDER_CONFIRM->value]);

    $unassignedOld = dqOrder($store, 'pending', null, false, now()->subDays(5));
    $overCapacity = dqOrder($store, 'confirmed', $assignee, true, now()->subDays(3));
    $unassignedNew = dqOrder($store, 'pending', null, false, now()->subDay());
    dqOrder($store, 'confirmed', $assignee, false);          // assigned within cap — excluded
    dqOrder($store, 'delivered', null, false);               // terminal unassigned — excluded
    dqOrder($store, 'returned', $assignee, true);            // terminal over-capacity — excluded
    dqOrder($store, 'cancelled', null, true);                // terminal over-capacity — excluded

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.order-distribution-queue')
        ->assertOk()
        ->assertSet('confirmationCount', 3)
        ->assertSet('confirmationQueue.0.id', (string) $unassignedOld->id)
        ->assertSet('confirmationQueue.1.id', (string) $unassignedNew->id)
        ->assertSet('confirmationQueue.2.id', (string) $overCapacity->id)
        ->assertSet('confirmationQueue.2.over_capacity', true)
        ->assertSet('confirmationQueue.0.assigned_to', null)
        ->assertSee('Over capacity')
        ->assertSee('Unassigned');
});

test('the tracking tab includes open unassigned and over-capacity shipments, excludes closed and within-cap ones', function () {
    [$user, $store] = dqUser();
    $tracker = dqMember($store, [StorePermissionEnum::CRM_ORDER_TRACKING->value]);

    $baseOrder = fn (string $statusKey) => dqOrder($store, $statusKey);

    $shippedUnassigned = dqTracking($baseOrder('shipped'), OrderTrackingStatus::SHIPPED->value);
    $inTransitOver = dqTracking($baseOrder('in_transit'), OrderTrackingStatus::IN_TRANSIT->value, $tracker, true);
    dqTracking($baseOrder('delivered'), OrderTrackingStatus::DELIVERED->value);      // terminal — excluded
    dqTracking($baseOrder('delivered'), OrderTrackingStatus::RETURNED->value, $tracker, true); // terminal — excluded
    dqTracking($baseOrder('shipped'), OrderTrackingStatus::SHIPPED->value, $tracker); // open, within cap — excluded

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.order-distribution-queue')
        ->assertOk()
        ->call('setTab', 'tracking')
        ->assertSet('trackingCount', 2)
        ->assertSet('trackingQueue.0.id', (string) $shippedUnassigned->id)
        ->assertSet('trackingQueue.1.id', (string) $inTransitOver->id)
        ->assertSet('trackingQueue.1.over_capacity', true)
        ->assertSet('trackingQueue.0.assigned_to', null);
});

test('reassigning an over-capacity confirmation item clears the flag and removes it from the queue live', function () {
    [$user, $store, $ownerMembership] = dqUser();
    $confirmAgent = dqMember($store, [StorePermissionEnum::ORDER_CONFIRM->value]);

    $overCapacity = dqOrder($store, 'confirmed', $confirmAgent, true);
    $unassigned = dqOrder($store, 'pending', null, false);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.order-distribution-queue')
        ->assertSet('confirmationCount', 2)
        ->call('openReassignModal', (string) $overCapacity->id)
        ->assertSet('reassignOpen', true)
        ->assertSet('reassignKind', 'confirm')
        ->assertSet('reassignCandidates', fn ($candidates) => is_array($candidates) && $candidates !== [])
        ->set('reassignMembershipId', (string) $ownerMembership->id)
        ->call('submitReassign')
        ->assertSet('reassignOpen', false)
        ->assertSet('confirmationCount', 1)
        ->assertSet('confirmationQueue.0.id', (string) $unassigned->id)
        ->assertDispatched('swal:toast', fn ($name, $params) => dqToastIcon($params) === 'success');

    expect($overCapacity->fresh()->assigned_to_membership_id)->toBe($ownerMembership->id)
        ->and($overCapacity->fresh()->over_capacity)->toBeFalse();
});

test('reassigning an over-capacity tracking item clears the flag and removes it from the tracking queue live', function () {
    [$user, $store, $ownerMembership] = dqUser();
    $trackAgent = dqMember($store, [StorePermissionEnum::CRM_ORDER_TRACKING->value]);

    $overCapacity = dqTracking(dqOrder($store, 'shipped'), OrderTrackingStatus::SHIPPED->value, $trackAgent, true);
    $openUnassigned = dqTracking(dqOrder($store, 'in_transit'), OrderTrackingStatus::IN_TRANSIT->value);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.order-distribution-queue')
        ->assertSet('trackingCount', 2)
        ->call('setTab', 'tracking')
        ->call('openReassignModal', (string) $overCapacity->id)
        ->assertSet('reassignOpen', true)
        ->assertSet('reassignKind', 'track')
        ->assertSet('reassignCandidates', fn ($candidates) => is_array($candidates) && $candidates !== [])
        ->set('reassignMembershipId', (string) $ownerMembership->id)
        ->call('submitReassign')
        ->assertSet('reassignOpen', false)
        ->assertSet('trackingCount', 1)
        ->assertSet('trackingQueue.0.id', (string) $openUnassigned->id)
        ->assertDispatched('swal:toast', fn ($name, $params) => dqToastIcon($params) === 'success');

    expect($overCapacity->fresh()->assigned_to_membership_id)->toBe($ownerMembership->id)
        ->and($overCapacity->fresh()->over_capacity)->toBeFalse();
});

test('reassigning refuses a member without the matching permission', function () {
    [$user, $store, $ownerMembership] = dqUser();
    $viewOnly = dqMember($store, [StorePermissionEnum::ORDER_VIEW->value]);

    $overCapacity = dqOrder($store, 'confirmed', $viewOnly, true);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.order-distribution-queue')
        ->call('openReassignModal', (string) $overCapacity->id)
        ->set('reassignMembershipId', (string) $viewOnly->id)
        ->call('submitReassign')
        ->assertDispatched('swal:toast', fn ($name, $params) => dqToastIcon($params) === 'error');

    expect($overCapacity->fresh()->assigned_to_membership_id)->toBe($viewOnly->id)
        ->and($overCapacity->fresh()->over_capacity)->toBeTrue();
});

test('both tabs render an empty state when nothing needs attention', function () {
    [$user, $store] = dqUser();
    dqOrder($store, 'confirmed', dqMember($store, [StorePermissionEnum::ORDER_CONFIRM->value]));
    dqOrder($store, 'delivered', null, false);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.order-distribution-queue')
        ->assertSet('confirmationCount', 0)
        ->assertSet('trackingCount', 0)
        ->assertSee('No confirmation items need attention')
        ->call('setTab', 'tracking')
        ->assertSee('No tracking shipments need attention');
});

test('the queue query stays flat as rows grow (no N+1)', function () {
    [$user, $store] = dqUser();
    $assignee = dqMember($store, [StorePermissionEnum::ORDER_CONFIRM->value]);
    $tracker = dqMember($store, [StorePermissionEnum::CRM_ORDER_TRACKING->value]);

    $makeRows = function (int $count) use ($store, $assignee, $tracker): void {
        foreach (range(1, $count) as $i) {
            dqOrder($store, $i % 2 === 0 ? 'pending' : 'confirmed', $i % 3 === 0 ? $assignee : null, $i % 2 === 0);
            $order = dqOrder($store, 'shipped');
            dqTracking($order, OrderTrackingStatus::SHIPPED->value, $i % 3 === 0 ? $tracker : null, $i % 2 === 0);
        }
    };

    $makeRows(4);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    $first = [];
    DB::listen(function ($q) use (&$first) {
        $first[] = $q->sql;
    });

    Volt::test('merchant.order-distribution-queue')->html();

    $makeRows(4);

    $second = [];
    DB::listen(function ($q) use (&$second) {
        $second[] = $q->sql;
    });

    Volt::test('merchant.order-distribution-queue')->html();

    $isDataQuery = fn (string $sql): bool => (bool) preg_match(
        '/\b(?:from|into|update|delete)\s+"?(?:orders|order_trackings|customers|statuses)\b/i',
        $sql,
    );

    $dataBase = count(array_filter($first, $isDataQuery));
    $dataGrown = count(array_filter($second, $isDataQuery));

    expect($dataGrown)->toBeLessThanOrEqual($dataBase + 2)
        ->and($dataBase)->toBeGreaterThan(1);
});