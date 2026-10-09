<?php

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

function dqOrder(Store $store, string $statusKey, ?StoreMembership $assignedTo = null, bool $overCapacity = false, ?Carbon $createdAt = null, ?Carbon $strandedAt = null): Order
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
        'stranded_at' => $strandedAt,
        'created_at' => $createdAt ?? now(),
        'updated_at' => $createdAt ?? now(),
    ]);
}

function dqTracking(Order $order, string $trackingStatus, ?StoreMembership $assignedTo = null, bool $overCapacity = false, ?Carbon $strandedAt = null): OrderTracking
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
        'stranded_at' => $strandedAt,
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

test('the confirmation tab includes stranded orders, ordering unassigned then stranded (oldest) then over-capacity', function () {
    [$user, $store] = dqUser();
    $assignee = dqMember($store, [StorePermissionEnum::ORDER_CONFIRM->value]);

    $unassignedOld = dqOrder($store, 'pending', null, false, now()->subDays(6));
    $strandedOld = dqOrder($store, 'pending', $assignee, false, now()->subDays(5), now()->subDays(5));
    $overCapacity = dqOrder($store, 'pending', $assignee, true, now()->subDays(3));
    $strandedNew = dqOrder($store, 'pending', $assignee, false, now()->subDays(1), now()->subDays(1));

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.order-distribution-queue')
        ->assertOk()
        ->assertSet('confirmationCount', 4)
        ->assertSet('confirmationQueue.0.id', (string) $unassignedOld->id)
        ->assertSet('confirmationQueue.1.id', (string) $strandedOld->id)
        ->assertSet('confirmationQueue.2.id', (string) $strandedNew->id)
        ->assertSet('confirmationQueue.3.id', (string) $overCapacity->id)
        ->assertSet('confirmationQueue.1.stranded', true)
        ->assertSet('confirmationQueue.1.assigned_to', $assignee->user->name)
        ->assertSet('confirmationQueue.3.over_capacity', true)
        ->assertSee('Stranded');
});

test('the tracking tab includes stranded shipments with the stranded badge, after unassigned', function () {
    [$user, $store] = dqUser();
    $tracker = dqMember($store, [StorePermissionEnum::CRM_ORDER_TRACKING->value]);

    $stranded = dqTracking(dqOrder($store, 'shipped'), OrderTrackingStatus::SHIPPED->value, $tracker, false, now()->subDays(2));
    $unassigned = dqTracking(dqOrder($store, 'shipped'), OrderTrackingStatus::SHIPPED->value);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.order-distribution-queue')
        ->assertSet('trackingCount', 2)
        ->call('setTab', 'tracking')
        ->assertSet('trackingQueue.0.id', (string) $unassigned->id)
        ->assertSet('trackingQueue.1.id', (string) $stranded->id)
        ->assertSet('trackingQueue.1.stranded', true)
        ->assertSet('trackingQueue.1.assigned_to', $tracker->user->name)
        ->assertSee('Stranded');
});

test('reassigning a stranded confirmation item clears stranded_at and removes it from the queue live', function () {
    [$user, $store, $ownerMembership] = dqUser();
    $confirmAgent = dqMember($store, [StorePermissionEnum::ORDER_CONFIRM->value]);

    $stranded = dqOrder($store, 'pending', $confirmAgent, false, now()->subDays(2), now()->subDay());
    $unassigned = dqOrder($store, 'pending', null, false);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.order-distribution-queue')
        ->assertSet('confirmationCount', 2)
        ->call('openReassignModal', (string) $stranded->id)
        ->assertSet('reassignKind', 'confirm')
        ->set('reassignMembershipId', (string) $ownerMembership->id)
        ->call('submitReassign')
        ->assertSet('reassignOpen', false)
        ->assertSet('confirmationCount', 1)
        ->assertSet('confirmationQueue.0.id', (string) $unassigned->id)
        ->assertDispatched('swal:toast', fn ($name, $params) => dqToastIcon($params) === 'success');

    expect($stranded->fresh()->assigned_to_membership_id)->toBe($ownerMembership->id)
        ->and($stranded->fresh()->stranded_at)->toBeNull();
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

test('the queue paginates each tab independently, preserving each tab page and keeping total badges accurate', function () {
    [$user, $store] = dqUser();

    $confirmIds = collect();
    foreach (range(1, 60) as $i) {
        $confirmIds->push((string) dqOrder($store, 'pending', null, false, now()->subMinutes(61 - $i))->id);
    }

    $trackingIds = collect();
    foreach (range(1, 60) as $i) {
        $trackingIds->push((string) dqTracking(dqOrder($store, 'delivered'), OrderTrackingStatus::SHIPPED->value)->id);
    }

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    $component = Volt::test('merchant.order-distribution-queue')
        ->assertSet('confirmationCount', 60)
        ->assertSet('trackingCount', 60)
        ->assertSet('confirmationPagination.total', 60)
        ->assertSet('confirmationPagination.per_page', 50)
        ->assertSet('confirmationPagination.last_page', 2);

    $confirmationPage1 = collect($component->get('confirmationQueue'));

    expect($confirmationPage1)->toHaveCount(50)
        ->and($confirmationPage1[0]['id'])->toBe($confirmIds[0]);

    // Confirmation page 2 shows the remaining rows, distinct from page 1.
    $component->call('setConfirmationPage', 2)
        ->assertSet('confirmationPage', 2)
        ->assertSet('confirmationCount', 60)
        ->assertSet('confirmationPagination.current_page', 2);

    $confirmationPage2 = collect($component->get('confirmationQueue'));

    expect($confirmationPage2)->toHaveCount(10)
        ->and($confirmationPage2->pluck('id')->all())->toBe($confirmIds->slice(50)->values()->all())
        ->and($confirmationPage2->pluck('id')->intersect($confirmationPage1->pluck('id')))->toBeEmpty();

    // Switching to tracking preserves the confirmation page; tracking starts fresh.
    $component->call('setTab', 'tracking')
        ->assertSet('confirmationPage', 2)
        ->assertSet('trackingPage', 1)
        ->assertSet('trackingCount', 60)
        ->assertSet('trackingPagination.last_page', 2);

    $trackingPage1 = collect($component->get('trackingQueue'));

    expect($trackingPage1)->toHaveCount(50);

    $component->call('setTrackingPage', 2)
        ->assertSet('trackingPage', 2)
        ->assertSet('trackingCount', 60);

    $trackingPage2 = collect($component->get('trackingQueue'));

    expect($trackingPage2)->toHaveCount(10)
        ->and($trackingPage2->pluck('id')->intersect($trackingPage1->pluck('id')))->toBeEmpty()
        ->and($trackingPage1->pluck('id')->concat($trackingPage2->pluck('id'))->sort()->values()->all())
        ->toBe($trackingIds->sort()->values()->all());

    // Back to confirmation: each tab keeps its own page.
    $component->call('setTab', 'confirmation')
        ->assertSet('confirmationPage', 2)
        ->assertSet('trackingPage', 2)
        ->assertSet('confirmationCount', 60)
        ->assertSet('confirmationPagination.current_page', 2);

    expect(collect($component->get('confirmationQueue'))->pluck('id')->all())->toBe($confirmIds->slice(50)->values()->all());
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
        '/\b(?:from|into|update|delete)\s+["`]?(?:orders|order_trackings|customers|statuses)\b/i',
        $sql,
    );

    $dataBase = count(array_filter($first, $isDataQuery));
    $dataGrown = count(array_filter($second, $isDataQuery));

    expect($dataGrown)->toBeLessThanOrEqual($dataBase + 2)
        ->and($dataBase)->toBeGreaterThan(1);
});

test('the queue search filters rows by order number, customer, and tracking number on both tabs', function () {
    [$user, $store] = dqUser();

    $needle = dqOrder($store, 'pending', null, false, now()->subDay());
    $other = dqOrder($store, 'pending', null, false, now()->subDay());

    $needleTracking = dqTracking($needle, OrderTrackingStatus::SHIPPED->value);
    dqTracking($other, OrderTrackingStatus::SHIPPED->value);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    $component = Volt::test('merchant.order-distribution-queue')
        ->assertSet('confirmationCount', 2)
        ->assertSet('trackingCount', 2);

    // By order number on the confirmation tab.
    $component->set('queueSearch', $needle->number)
        ->assertSet('confirmationCount', 1)
        ->assertSet('confirmationQueue.0.id', (string) $needle->id);

    // By customer name, restricted to the matching order.
    $component->set('queueSearch', $needle->customer->name)
        ->assertSet('confirmationCount', 1)
        ->assertSet('confirmationQueue.0.id', (string) $needle->id);

    // The tracking tab searches the tracking number too.
    $component->set('queueSearch', $needleTracking->tracking_number)
        ->call('setTab', 'tracking')
        ->assertSet('trackingCount', 1)
        ->assertSet('trackingQueue.0.id', (string) $needleTracking->id);

    // Clearing the search restores the full tab.
    $component->set('queueSearch', '')
        ->assertSet('trackingCount', 2);

    expect($needle->fresh()->id)->not->toBe($other->id);
});
