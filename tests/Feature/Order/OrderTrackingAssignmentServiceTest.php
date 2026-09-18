<?php

use App\Domains\Order\Models\ConfirmationShift;
use App\Domains\Order\Services\OrderTrackingAssignmentService;
use App\Enums\Store\OrderTrackingStatus;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Orders\Order;
use App\Models\Orders\OrderTracking;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function (): void {
    config(['app.timezone' => 'Africa/Algiers']);
    \Illuminate\Support\Carbon::setTestNow(\Illuminate\Support\Carbon::create(2026, 5, 4, 10, 0, 0, 'Africa/Algiers'));
    test()->artisan('db:seed', ['--class' => \Database\Seeders\SystemStatusesSeeder::class, '--force' => true]);
});

afterEach(function (): void {
    \Illuminate\Support\Carbon::setTestNow();
});

function trackingStore(): Store
{
    $owner = User::factory()->create();

    return Store::create([
        'user_id' => $owner->id,
        'name' => 'Tracking Assignment Store',
        'slug' => 'trk-assign-'.uniqid(),
        'status' => 'active',
        'landing_template' => 'catalog',
    ]);
}

function trackingMembership(Store $store, array $permissions = [StorePermissionEnum::CRM_ORDER_TRACKING->value]): StoreMembership
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

function trackShift(Store $store, StoreMembership $membership, ?int $cap = null): ConfirmationShift
{
    return ConfirmationShift::create([
        'store_id' => $store->id,
        'membership_id' => $membership->id,
        'shift_type' => 'morning',
        'start_time' => '08:00',
        'end_time' => '17:00',
        'days_of_week' => [1, 2, 3, 4, 5],
        'is_active' => true,
        'role_scope' => 'track',
        'max_concurrent_orders' => $cap,
    ]);
}

function trackingOrder(Store $store): Order
{
    $status = \App\Models\Status::system()->forType('order')->where('key', 'pending')->first();

    return Order::create([
        'store_id' => $store->id,
        'status_id' => $status?->id,
        'number' => (new Order(['store_id' => $store->id]))->nextOrderNumber(),
        'total_amount' => 500,
        'delivery_type' => 'home',
        'payment_method' => 'cod',
    ]);
}

function openTracking(Store $store, Order $order): OrderTracking
{
    return OrderTracking::create([
        'store_id' => $store->id,
        'order_id' => $order->id,
        'tracking_status' => OrderTrackingStatus::SHIPPED->value,
        'shipped_at' => now(),
    ]);
}

test('a tracking agent on an active track shift is assigned the tracking', function () {
    $store = trackingStore();
    $agent = trackingMembership($store);
    trackShift($store, $agent);

    $tracking = openTracking($store, trackingOrder($store));

    app(OrderTrackingAssignmentService::class)->assign($tracking);

    expect($tracking->fresh()->assigned_to_membership_id)->toBe($agent->id)
        ->and($tracking->fresh()->assignment_method)->toBe('auto')
        ->and($tracking->fresh()->assigned_at)->not->toBeNull();
});

test('the track-scoped cap is enforced when resolving candidates', function () {
    $store = trackingStore();
    $capped = trackingMembership($store);
    $free = trackingMembership($store);
    trackShift($store, $capped, 1);
    trackShift($store, $free);

    // $capped already holds one open tracking → at its track cap of 1.
    $existing = openTracking($store, trackingOrder($store));
    $existing->update([
        'assigned_to_membership_id' => $capped->id,
        'assigned_at' => now()->subMinutes(5),
        'assignment_method' => 'auto',
    ]);

    $tracking = openTracking($store, trackingOrder($store));

    app(OrderTrackingAssignmentService::class)->assign($tracking);

    expect($tracking->fresh()->assigned_to_membership_id)->toBe($free->id);
});

test('a confirm-scoped cap never limits the tracking quota', function () {
    $store = trackingStore();
    $agent = trackingMembership($store);
    trackShift($store, $agent);

    ConfirmationShift::create([
        'store_id' => $store->id,
        'membership_id' => $agent->id,
        'shift_type' => 'morning',
        'start_time' => '08:00',
        'end_time' => '17:00',
        'days_of_week' => [1, 2, 3, 4, 5],
        'is_active' => true,
        'role_scope' => 'confirm',
        'max_concurrent_orders' => 1,
    ]);

    // One open tracking already held → a wrongly-applied confirm cap of 1 would
    // block the agent; the track cap is null so the agent stays eligible.
    $existing = openTracking($store, trackingOrder($store));
    $existing->update([
        'assigned_to_membership_id' => $agent->id,
        'assigned_at' => now()->subMinutes(5),
        'assignment_method' => 'auto',
    ]);

    $tracking = openTracking($store, trackingOrder($store));

    app(OrderTrackingAssignmentService::class)->assign($tracking);

    expect($tracking->fresh()->assigned_to_membership_id)->toBe($agent->id);
});

test('load balances toward the least loaded on-track agent', function () {
    $store = trackingStore();
    $a = trackingMembership($store);
    $b = trackingMembership($store);
    trackShift($store, $a);
    trackShift($store, $b);

    $existing = openTracking($store, trackingOrder($store));
    $existing->update([
        'assigned_to_membership_id' => $a->id,
        'assigned_at' => now()->subMinutes(5),
        'assignment_method' => 'auto',
    ]);

    $tracking = openTracking($store, trackingOrder($store));

    app(OrderTrackingAssignmentService::class)->assign($tracking);

    expect($tracking->fresh()->assigned_to_membership_id)->toBe($b->id);
});

test('load balances ties by oldest last assignment', function () {
    $store = trackingStore();
    $a = trackingMembership($store);
    $b = trackingMembership($store);
    trackShift($store, $a);
    trackShift($store, $b);

    $recent = openTracking($store, trackingOrder($store));
    $recent->update([
        'assigned_to_membership_id' => $a->id,
        'assigned_at' => now()->subMinutes(5),
        'assignment_method' => 'auto',
    ]);
    $old = openTracking($store, trackingOrder($store));
    $old->update([
        'assigned_to_membership_id' => $b->id,
        'assigned_at' => now()->subMinutes(50),
        'assignment_method' => 'auto',
    ]);

    // Both hold one open tracking → tie goes to the least-recently assigned member.
    $tracking = openTracking($store, trackingOrder($store));

    app(OrderTrackingAssignmentService::class)->assign($tracking);

    expect($tracking->fresh()->assigned_to_membership_id)->toBe($b->id);
});

test('leaves the tracking unassigned when no track-scoped agent is on shift', function () {
    $store = trackingStore();
    $agent = trackingMembership($store);

    // Confirm-scoped shift only → not "on a track shift".
    ConfirmationShift::create([
        'store_id' => $store->id,
        'membership_id' => $agent->id,
        'shift_type' => 'morning',
        'start_time' => '08:00',
        'end_time' => '17:00',
        'days_of_week' => [1, 2, 3, 4, 5],
        'is_active' => true,
        'role_scope' => 'confirm',
    ]);

    $tracking = openTracking($store, trackingOrder($store));

    app(OrderTrackingAssignmentService::class)->assign($tracking);

    expect($tracking->fresh()->assigned_to_membership_id)->toBeNull()
        ->and($tracking->fresh()->assignment_method)->toBeNull();
});

test('a member with only ORDER_CONFIRM permission is never a tracking candidate', function () {
    $store = trackingStore();
    $confirmer = trackingMembership($store, [
        StorePermissionEnum::ORDER_VIEW->value,
        StorePermissionEnum::ORDER_CONFIRM->value,
    ]);
    trackShift($store, $confirmer);

    $service = app(OrderTrackingAssignmentService::class);

    $tracking = openTracking($store, trackingOrder($store));
    $service->assign($tracking);

    expect($tracking->fresh()->assigned_to_membership_id)->toBeNull();

    // …and a CRM_ORDER_TRACKING member IS selected when one exists.
    $agent = trackingMembership($store);
    trackShift($store, $agent);

    $second = openTracking($store, trackingOrder($store));
    $service->assign($second);

    expect($second->fresh()->assigned_to_membership_id)->toBe($agent->id);
});

test('reassign sets the manual assignment per the acting membership', function () {
    $store = trackingStore();
    $to = trackingMembership($store);
    $by = trackingMembership($store);

    $tracking = openTracking($store, trackingOrder($store));

    app(OrderTrackingAssignmentService::class)->reassign($tracking, $to, $by);

    expect($tracking->fresh()->assigned_to_membership_id)->toBe($to->id)
        ->and($tracking->fresh()->assigned_by_membership_id)->toBe($by->id)
        ->and($tracking->fresh()->assignment_method)->toBe('manual');
});

test('reassign refuses a member of a different store', function () {
    $store = trackingStore();
    $other = trackingStore();
    $to = trackingMembership($other);
    $by = trackingMembership($store);

    $tracking = openTracking($store, trackingOrder($store));

    expect(fn () => app(OrderTrackingAssignmentService::class)->reassign($tracking, $to, $by))
        ->toThrow(\InvalidArgumentException::class);
});