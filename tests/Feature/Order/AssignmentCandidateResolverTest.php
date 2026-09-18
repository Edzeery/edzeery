<?php

use App\Domains\Order\Models\ConfirmationShift;
use App\Domains\Order\Support\AssignmentCandidateResolver;
use App\Enums\Store\OrderTrackingStatus;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Orders\Order;
use App\Models\Orders\OrderTracking;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Models\User;
use Illuminate\Support\Facades\DB;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function (): void {
    config(['app.timezone' => 'Africa/Algiers']);
    \Illuminate\Support\Carbon::setTestNow(\Illuminate\Support\Carbon::create(2026, 5, 4, 10, 0, 0, 'Africa/Algiers'));
    test()->artisan('db:seed', ['--class' => \Database\Seeders\SystemStatusesSeeder::class, '--force' => true]);
});

afterEach(function (): void {
    \Illuminate\Support\Carbon::setTestNow();
});

function resolverStore(): Store
{
    $owner = User::factory()->create();

    return Store::create([
        'user_id' => $owner->id,
        'name' => 'Resolver Store',
        'slug' => 'resolver-'.uniqid(),
        'status' => 'active',
        'landing_template' => 'catalog',
    ]);
}

function resolverMembership(Store $store, array $permissions): StoreMembership
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

function resolverShift(Store $store, StoreMembership $membership, string $roleScope, ?int $cap = null): ConfirmationShift
{
    return ConfirmationShift::create([
        'store_id' => $store->id,
        'membership_id' => $membership->id,
        'shift_type' => 'morning',
        'start_time' => '08:00',
        'end_time' => '17:00',
        'days_of_week' => [1, 2, 3, 4, 5],
        'is_active' => true,
        'role_scope' => $roleScope,
        'max_concurrent_orders' => $cap,
    ]);
}

function resolverOrder(Store $store): Order
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

function resolverTracking(Store $store, Order $order, string $status = OrderTrackingStatus::SHIPPED->value): OrderTracking
{
    return OrderTracking::create([
        'store_id' => $store->id,
        'order_id' => $order->id,
        'tracking_status' => $status,
        'shipped_at' => now(),
    ]);
}

test('confirm resolver lists only ORDER_CONFIRM holders', function () {
    $store = resolverStore();
    $confirmer = resolverMembership($store, [
        StorePermissionEnum::ORDER_VIEW->value,
        StorePermissionEnum::ORDER_CONFIRM->value,
    ]);
    resolverMembership($store, [StorePermissionEnum::CRM_ORDER_TRACKING->value]);

    $candidates = app(AssignmentCandidateResolver::class)
        ->resolve($store->id, 'confirm', StorePermissionEnum::ORDER_CONFIRM->value);

    expect($candidates->pluck('id')->all())->toBe([(string) $confirmer->id])
        ->and($candidates->first()['name'])->toBe($confirmer->user->name);
});

test('track resolver lists only CRM_ORDER_TRACKING holders', function () {
    $store = resolverStore();
    $tracker = resolverMembership($store, [StorePermissionEnum::CRM_ORDER_TRACKING->value]);
    resolverMembership($store, [StorePermissionEnum::ORDER_CONFIRM->value]);

    $candidates = app(AssignmentCandidateResolver::class)
        ->resolve($store->id, 'track', StorePermissionEnum::CRM_ORDER_TRACKING->value);

    expect($candidates->pluck('id')->all())->toBe([(string) $tracker->id]);
});

test('confirm resolver annotates open load, cap and on-shift state', function () {
    $store = resolverStore();
    $confirmer = resolverMembership($store, [StorePermissionEnum::ORDER_CONFIRM->value]);
    resolverShift($store, $confirmer, 'confirm', 3);

    $held = resolverOrder($store);
    $held->update(['assigned_to_membership_id' => $confirmer->id]);
    resolverOrder($store); // unassigned → not counted

    $candidate = app(AssignmentCandidateResolver::class)
        ->resolve($store->id, 'confirm', StorePermissionEnum::ORDER_CONFIRM->value)
        ->first();

    expect($candidate['open'])->toBe(1)
        ->and($candidate['cap'])->toBe(3)
        ->and($candidate['on_shift'])->toBeTrue()
        ->and($candidate['dual_role'])->toBeFalse();
});

test('track resolver counts open statuses only and skips terminal deliveries', function () {
    $store = resolverStore();
    $tracker = resolverMembership($store, [StorePermissionEnum::CRM_ORDER_TRACKING->value]);
    resolverShift($store, $tracker, 'track', 2);

    $openOne = resolverTracking($store, resolverOrder($store));
    resolverTracking($store, resolverOrder($store)); // open, unassigned → not counted
    resolverTracking($store, resolverOrder($store), OrderTrackingStatus::DELIVERED->value);

    $openOne->update(['assigned_to_membership_id' => $tracker->id]);

    $candidate = app(AssignmentCandidateResolver::class)
        ->resolve($store->id, 'track', StorePermissionEnum::CRM_ORDER_TRACKING->value)
        ->first();

    expect($candidate['open'])->toBe(1)
        ->and($candidate['cap'])->toBe(2)
        ->and($candidate['on_shift'])->toBeTrue();
});

test('a member with no capped shift is uncapped and shows as dual-role when holding both permissions', function () {
    $store = resolverStore();
    $dual = resolverMembership($store, [
        StorePermissionEnum::ORDER_CONFIRM->value,
        StorePermissionEnum::CRM_ORDER_TRACKING->value,
    ]);

    $candidate = app(AssignmentCandidateResolver::class)
        ->resolve($store->id, 'track', StorePermissionEnum::CRM_ORDER_TRACKING->value)
        ->first();

    expect($candidate['cap'])->toBeNull()
        ->and($candidate['open'])->toBe(0)
        ->and($candidate['on_shift'])->toBeFalse()
        ->and($candidate['dual_role'])->toBeTrue();
});

test('over-capacity members remain selectable for manual reassignment', function () {
    $store = resolverStore();
    $full = resolverMembership($store, [StorePermissionEnum::CRM_ORDER_TRACKING->value]);
    resolverShift($store, $full, 'track', 1);

    $held = resolverTracking($store, resolverOrder($store));
    $held->update(['assigned_to_membership_id' => $full->id]);

    $candidates = app(AssignmentCandidateResolver::class)
        ->resolve($store->id, 'track', StorePermissionEnum::CRM_ORDER_TRACKING->value);

    $fullCandidate = $candidates->firstWhere('id', (string) $full->id);

    expect($fullCandidate['open'])->toBe(1)
        ->and($fullCandidate['cap'])->toBe(1);
});

test('resolver issue a constant number of queries regardless of candidate count', function () {
    $singleStore = resolverStore();
    $single = resolverMembership($singleStore, [StorePermissionEnum::CRM_ORDER_TRACKING->value]);
    resolverShift($singleStore, $single, 'track');

    $bulkStore = resolverStore();
    foreach (range(1, 4) as $cap) {
        $member = resolverMembership($bulkStore, [StorePermissionEnum::CRM_ORDER_TRACKING->value]);
        resolverShift($bulkStore, $member, 'track', $cap);
    }

    DB::connection()->flushQueryLog();
    DB::connection()->enableQueryLog();
    app(AssignmentCandidateResolver::class)
        ->resolve($singleStore->id, 'track', StorePermissionEnum::CRM_ORDER_TRACKING->value);
    $queriesForOne = count(DB::getQueryLog());

    DB::connection()->flushQueryLog();
    app(AssignmentCandidateResolver::class)
        ->resolve($bulkStore->id, 'track', StorePermissionEnum::CRM_ORDER_TRACKING->value);
    $queriesForFour = count(DB::getQueryLog());

    expect($queriesForOne)->toBe($queriesForFour);
});