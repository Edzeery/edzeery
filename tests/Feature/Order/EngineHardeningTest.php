<?php

use App\Domains\Order\Jobs\ShiftHandoverJob;
use App\Domains\Order\Models\ConfirmationShift;
use App\Domains\Order\Services\OrderAssignmentService;
use App\Domains\Order\Services\OrderTrackingAssignmentService;
use App\Enums\Store\OrderTrackingStatus;
use App\Enums\Store\StorePermissionEnum;
use App\Enums\Store\StoreRoleEnum;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Models\Orders\OrderTracking;
use App\Models\Products\Product;
use App\Models\Products\ProductVariant;
use App\Models\Status;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Models\User;
use App\Services\Stores\StoreTeamService;
use App\Support\StoreRoles;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Livewire\Volt\Volt;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

/*
|--------------------------------------------------------------------------
| PHASE 35.2-C — engine hardening: classification, handover contract,
| concurrency lock, uniform tie-break and event-driven sweeps
|--------------------------------------------------------------------------
|
| Frozen time is Monday 2026-05-04 10:00 (Africa/Algiers): "today" is
| day-of-week 1, so on-shift = days containing 1 and off-shift = [2..7].
|
*/

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function (): void {
    config(['app.timezone' => 'Africa/Algiers']);
    Carbon::setTestNow(Carbon::create(2026, 5, 4, 10, 0, 0, 'Africa/Algiers'));
    $this->seed(Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\StoreRolesAndPermissionsSeeder::class);
    test()->artisan('db:seed', ['--class' => Database\Seeders\SystemStatusesSeeder::class, '--force' => true]);
});

afterEach(function (): void {
    Carbon::setTestNow();
});

function hardenStore(): Store
{
    $owner = User::factory()->create();

    return Store::create([
        'user_id' => $owner->id,
        'name' => 'Hardening Store',
        'slug' => 'harden-'.uniqid(),
        'status' => 'active',
        'landing_template' => 'catalog',
    ]);
}

function hardenMember(Store $store, array $permissions): StoreMembership
{
    $membership = StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => User::factory()->create()->id,
        'invited_by' => $store->user_id,
        'is_active' => true,
        'role' => 'staff',
    ]);

    $membership->syncPermissions($permissions);

    return $membership;
}

function hardenPermissions(): array
{
    return [
        StorePermissionEnum::ORDER_VIEW->value,
        StorePermissionEnum::ORDER_CONFIRM->value,
    ];
}

function hardenTrackPermissions(): array
{
    return [
        StorePermissionEnum::ORDER_VIEW->value,
        StorePermissionEnum::CRM_ORDER_TRACKING->value,
    ];
}

function hardenShift(Store $store, StoreMembership $member, array $days, string $roleScope = 'confirm'): ConfirmationShift
{
    return ConfirmationShift::create([
        'store_id' => $store->id,
        'membership_id' => $member->id,
        'shift_type' => 'morning',
        'start_time' => '08:00',
        'end_time' => '17:00',
        'days_of_week' => $days,
        'is_active' => true,
        'role_scope' => $roleScope,
    ]);
}

function hardenOrder(Store $store, string $key = 'pending'): Order
{
    $status = Status::system()->forType('order')->where('key', $key)->first();

    $order = Order::create([
        'store_id' => $store->id,
        'status_id' => $status?->id,
        'number' => (new Order(['store_id' => $store->id]))->nextOrderNumber(),
        'total_amount' => 500,
        'delivery_type' => 'home',
        'payment_method' => 'cod',
    ]);

    $product = Product::create([
        'store_id' => $store->id,
        'name' => 'Hardening Product',
        'slug' => 'hardening-'.uniqid(),
        'sku' => 'HARD-'.strtoupper(uniqid()),
        'type' => 'simple',
        'price' => 500,
        'is_active' => true,
    ]);

    $variant = ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Default',
        'sku' => 'HARD-V-'.uniqid(),
        'price' => 500,
        'is_active' => true,
    ]);

    OrderItem::create([
        'store_id' => $store->id,
        'order_id' => $order->id,
        'product_variant_id' => $variant->id,
        'product_id' => $product->id,
        'quantity' => 1,
        'price' => 500,
        'subtotal' => 500,
    ]);

    return $order;
}

function hardenClaim(Order $order, StoreMembership $member): Order
{
    $order->update([
        'assigned_to_membership_id' => $member->id,
        'assigned_at' => now(),
        'assignment_method' => 'auto',
    ]);

    return $order;
}

function hardenTracking(Order $order, StoreMembership $member): OrderTracking
{
    return OrderTracking::create([
        'store_id' => $order->store_id,
        'order_id' => $order->id,
        'tracking_status' => OrderTrackingStatus::SHIPPED->value,
        'shipped_at' => now(),
        'assigned_to_membership_id' => $member->id,
        'assigned_at' => now(),
        'assignment_method' => 'auto',
    ]);
}

test('the distribution classification covers every seeded order status exactly once', function () {
    $seeded = Status::system()->forType('order')->pluck('key')->all();
    $class = config('order-distribution');

    $classified = array_merge(
        $class['confirmation_statuses'],
        $class['fulfillment_statuses'],
        $class['closed_statuses'],
    );

    expect(array_diff($seeded, $classified))->toBe([])
        ->and(array_diff($classified, $seeded))->toBe([])
        ->and($classified)->toHaveCount(count(array_unique($classified)))
        ->and($class['confirmation_statuses'])->toContain('pending', 'no_answer_1', 'no_answer_3', 'postponed', 'on_hold')
        ->and($class['closed_statuses'])->toContain('paid', 'draft', 'wrong_number');
});

test('shift handover replaces an off-shift confirmation assignee with method handover', function () {
    $store = hardenStore();
    $offShift = hardenMember($store, hardenPermissions());
    hardenShift($store, $offShift, [2, 3, 4, 5, 6, 7]); // every day but today (Monday)
    $onShift = hardenMember($store, hardenPermissions());
    hardenShift($store, $onShift, [1, 2, 3, 4, 5]);

    $order = hardenClaim(hardenOrder($store), $offShift);

    Carbon::setTestNow(now()->addHour()); // 11:00 — the claim happened at 10:00

    app(OrderAssignmentService::class)->handleShiftHandover($store);

    $fresh = $order->fresh();

    expect($fresh->assigned_to_membership_id)->toBe($onShift->id)
        ->and($fresh->assignment_method)->toBe('handover')
        ->and($fresh->assigned_by_membership_id)->toBeNull()
        ->and($fresh->over_capacity)->toBeFalse()
        ->and($fresh->stranded_at)->toBeNull()
        ->and($fresh->assigned_at->equalTo(now()))->toBeTrue();
});

test('shift handover never touches fulfillment or closed status assignments', function () {
    $store = hardenStore();
    $offShift = hardenMember($store, hardenPermissions());
    hardenShift($store, $offShift, [2, 3, 4, 5, 6, 7]);
    $onShift = hardenMember($store, hardenPermissions());
    hardenShift($store, $onShift, [1, 2, 3, 4, 5]);

    $shipped = hardenClaim(hardenOrder($store, 'shipped'), $offShift);
    $paid = hardenClaim(hardenOrder($store, 'paid'), $offShift);

    app(OrderAssignmentService::class)->handleShiftHandover($store);

    foreach ([$shipped, $paid] as $order) {
        expect($order->fresh())
            ->assigned_to_membership_id->toBe($offShift->id)
            ->assignment_method->toBe('auto')
            ->stranded_at->toBeNull();
    }
});

test('shift handover keeps the assignment and flags stranded_at once when nobody is eligible', function () {
    $store = hardenStore();
    $offShift = hardenMember($store, hardenPermissions());
    hardenShift($store, $offShift, [2, 3, 4, 5, 6, 7]);

    $order = hardenClaim(hardenOrder($store), $offShift);

    app(OrderAssignmentService::class)->handleShiftHandover($store);

    expect($order->fresh()->assigned_to_membership_id)->toBe($offShift->id)
        ->and($order->fresh()->assignment_method)->toBe('auto')
        ->and($order->fresh()->stranded_at)->not->toBeNull();

    $strandedAt = $order->fresh()->stranded_at;

    // Advance keeping the frozen clock's timezone: now() hands back the
    // default (UTC) zone, and Carbon parses stored datetimes in the
    // test-now's zone, which would otherwise flip and break comparisons.
    Carbon::setTestNow(Carbon::getTestNow()->copy()->addMinutes(30));
    app(OrderAssignmentService::class)->handleShiftHandover($store);

    expect($order->fresh()->stranded_at->equalTo($strandedAt))->toBeTrue();
});

test('a later sweep replaces a stranded order and clears stranded_at', function () {
    $store = hardenStore();
    $offShift = hardenMember($store, hardenPermissions());
    hardenShift($store, $offShift, [2, 3, 4, 5, 6, 7]);

    $order = hardenClaim(hardenOrder($store), $offShift);
    app(OrderAssignmentService::class)->handleShiftHandover($store);

    expect($order->fresh()->stranded_at)->not->toBeNull();

    $onShift = hardenMember($store, hardenPermissions());
    hardenShift($store, $onShift, [1, 2, 3, 4, 5]);

    app(OrderAssignmentService::class)->handleShiftHandover($store);

    expect($order->fresh())
        ->assigned_to_membership_id->toBe($onShift->id)
        ->assignment_method->toBe('handover')
        ->stranded_at->toBeNull();
});

test('a contended distribution lock skips the pass without throwing or touching state', function () {
    $store = hardenStore();
    $onShift = hardenMember($store, hardenPermissions());
    hardenShift($store, $onShift, [1, 2, 3, 4, 5]);

    $order = hardenOrder($store);
    $lock = Cache::lock("distribution:{$store->id}:confirm", 30);

    expect($lock->get())->toBeTrue();

    // Waits the 5s block window, then logs and returns with state untouched.
    app(OrderAssignmentService::class)->assign($order);

    expect($order->fresh()->assigned_to_membership_id)->toBeNull()
        ->and($order->fresh()->assignment_method)->toBeNull();

    $lock->release();

    app(OrderAssignmentService::class)->assign($order);

    expect($order->fresh()->assigned_to_membership_id)->toBe($onShift->id);
});

test('fully tied candidates win the tie uniformly across resets', function () {
    $store = hardenStore();
    $a = hardenMember($store, hardenPermissions());
    $b = hardenMember($store, hardenPermissions());
    hardenShift($store, $a, [1, 2, 3, 4, 5]);
    hardenShift($store, $b, [1, 2, 3, 4, 5]);

    $order = hardenOrder($store);
    $winners = [];

    foreach (range(1, 15) as $round) {
        app(OrderAssignmentService::class)->assign($order);

        $winners[] = $order->fresh()->assigned_to_membership_id;

        $order->update([
            'assigned_to_membership_id' => null,
            'assigned_at' => null,
            'assignment_method' => null,
        ]);
    }

    expect($winners)->not->toContain(null)
        ->and(array_unique($winners))->toHaveCount(2);
});

test('shift handover replaces an off-shift tracker with method handover', function () {
    $store = hardenStore();
    $offShift = hardenMember($store, hardenTrackPermissions());
    hardenShift($store, $offShift, [2, 3, 4, 5, 6, 7], 'track');
    $onShift = hardenMember($store, hardenTrackPermissions());
    hardenShift($store, $onShift, [1, 2, 3, 4, 5], 'track');

    $tracking = hardenTracking(hardenOrder($store), $offShift);

    app(OrderTrackingAssignmentService::class)->handleShiftHandover($store);

    expect($tracking->fresh())
        ->assigned_to_membership_id->toBe($onShift->id)
        ->assignment_method->toBe('handover')
        ->assigned_by_membership_id->toBeNull()
        ->over_capacity->toBeFalse()
        ->stranded_at->toBeNull();
});

test('shift handover keeps the tracking and flags stranded_at when no tracker is eligible', function () {
    $store = hardenStore();
    $offShift = hardenMember($store, hardenTrackPermissions());
    hardenShift($store, $offShift, [2, 3, 4, 5, 6, 7], 'track');

    $tracking = hardenTracking(hardenOrder($store), $offShift);

    app(OrderTrackingAssignmentService::class)->handleShiftHandover($store);

    $fresh = $tracking->fresh();

    expect($fresh->assigned_to_membership_id)->toBe($offShift->id)
        ->and($fresh->assignment_method)->toBe('auto')
        ->and($fresh->stranded_at)->not->toBeNull();
});

test('team member updates and removals dispatch the shift handover sweep', function () {
    Bus::fake([ShiftHandoverJob::class]);

    $store = hardenStore();
    $member = hardenMember($store, hardenPermissions());

    app(StoreTeamService::class)->updateMember($store, $member, [
        'name' => 'Renamed Agent',
        'email' => $member->user->email,
        'is_active' => true,
    ]);

    Bus::assertDispatched(ShiftHandoverJob::class, fn (ShiftHandoverJob $job) => $job->store->is($store));

    app(StoreTeamService::class)->removeMember($member);

    Bus::assertDispatchedTimes(ShiftHandoverJob::class, 2);
});

test('the teams ui member toggle dispatches the shift handover sweep', function () {
    Bus::fake([ShiftHandoverJob::class]);

    $owner = roleUser('merchant');
    $owner->assignRole(Role::findOrCreate('owner', 'merchant'));

    $store = Store::create([
        'user_id' => $owner->id,
        'name' => 'Hardening Teams',
        'slug' => 'harden-teams-'.uniqid(),
        'status' => 'active',
    ]);

    $ownerMembership = StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $owner->id,
        'invited_by' => $owner->id,
        'is_active' => true,
        'role' => StoreRoleEnum::OWNER->value,
    ]);
    $ownerMembership->syncPermissions(StoreRoles::permissions(StoreRoleEnum::OWNER));

    $staff = hardenMember($store, hardenPermissions());

    actingAs($owner)->withSession(['current_store_id' => $store->id]);
    app(\App\Support\StoreContext::class)->clear();

    Volt::test('merchant.teams.index')->call('toggleActive', $staff->id);

    Bus::assertDispatched(ShiftHandoverJob::class, fn (ShiftHandoverJob $job) => $job->store->is($store));
    expect($staff->fresh()->is_active)->toBeFalse();
});

test('order-settings shift save toggle and delete dispatch the shift handover sweep', function () {
    Bus::fake([ShiftHandoverJob::class]);

    $owner = roleUser('merchant');
    $owner->assignRole(Role::findOrCreate('owner', 'merchant'));

    $store = Store::create([
        'user_id' => $owner->id,
        'name' => 'Hardening Shifts',
        'slug' => 'harden-shifts-'.uniqid(),
        'status' => 'active',
    ]);

    $ownerMembership = StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $owner->id,
        'invited_by' => $owner->id,
        'is_active' => true,
        'role' => StoreRoleEnum::OWNER->value,
    ]);
    $ownerMembership->syncPermissions([StorePermissionEnum::ORDER_MANAGE->value]);

    $agent = hardenMember($store, hardenPermissions());

    actingAs($owner)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.order-settings')
        ->call('openShiftModal')
        ->set('shiftForm.membership_id', $agent->id)
        ->call('saveShift')
        ->assertHasNoErrors();

    $shift = ConfirmationShift::sole();

    Volt::test('merchant.order-settings')->call('toggleShiftActive', $shift->id);
    Volt::test('merchant.order-settings')->call('deleteShift', $shift->id);

    Bus::assertDispatchedTimes(ShiftHandoverJob::class, 3);
});
