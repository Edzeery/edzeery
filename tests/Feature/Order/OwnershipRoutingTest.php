<?php

use App\Domains\Order\Models\ConfirmationProductAssignment;
use App\Domains\Order\Models\ConfirmationShift;
use App\Domains\Order\Services\OrderAssignmentService;
use App\Domains\Order\Services\OrderTrackingAssignmentService;
use App\Enums\Store\OrderTrackingStatus;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Models\Orders\OrderTracking;
use App\Models\Products\Product;
use App\Models\Products\ProductVariant;
use App\Models\Status;
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

function ownerStore(): Store
{
    $owner = User::factory()->create();

    return Store::create([
        'user_id' => $owner->id,
        'name' => 'Ownership Store',
        'slug' => 'owner-'.uniqid(),
        'status' => 'active',
        'landing_template' => 'catalog',
    ]);
}

function ownerMember(Store $store, array $permissions): StoreMembership
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

function ownerShift(Store $store, StoreMembership $member, string $roleScope = 'confirm', ?int $cap = null): ConfirmationShift
{
    return ConfirmationShift::create([
        'store_id' => $store->id,
        'membership_id' => $member->id,
        'shift_type' => 'morning',
        'start_time' => '08:00',
        'end_time' => '17:00',
        'days_of_week' => [1, 2, 3, 4, 5],
        'is_active' => true,
        'role_scope' => $roleScope,
        'max_concurrent_orders' => $cap,
    ]);
}

function ownerProduct(Store $store, string $name): Product
{
    return Product::create([
        'store_id' => $store->id,
        'name' => $name,
        'slug' => str($name)->slug().'-'.uniqid(),
        'sku' => strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 4)).'-'.strtoupper(uniqid()),
        'type' => 'simple',
        'price' => 500,
        'is_active' => true,
    ]);
}

function ownerOrder(Store $store, Product ...$products): Order
{
    $status = Status::system()->forType('order')->where('key', 'pending')->first();

    $order = Order::create([
        'store_id' => $store->id,
        'status_id' => $status?->id,
        'number' => (new Order(['store_id' => $store->id]))->nextOrderNumber(),
        'total_amount' => 500 * count($products),
        'delivery_type' => 'home',
        'payment_method' => 'cod',
    ]);

    foreach ($products as $product) {
        $variant = ProductVariant::create([
            'store_id' => $store->id,
            'product_id' => $product->id,
            'name' => 'Default',
            'sku' => 'own-v-'.uniqid(),
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
    }

    return $order;
}

function ownerClaim(Order $order, StoreMembership $member): Order
{
    $order->update([
        'assigned_to_membership_id' => $member->id,
        'assigned_at' => now(),
        'assignment_method' => 'auto',
    ]);

    return $order;
}

function ownsProduct(Store $store, StoreMembership $member, Product $product): ConfirmationProductAssignment
{
    return ConfirmationProductAssignment::create([
        'store_id' => $store->id,
        'membership_id' => $member->id,
        'product_id' => $product->id,
    ]);
}

function confirmPermissions(): array
{
    return [
        StorePermissionEnum::ORDER_VIEW->value,
        StorePermissionEnum::ORDER_CONFIRM->value,
    ];
}

function trackPermissions(): array
{
    return [
        StorePermissionEnum::ORDER_VIEW->value,
        StorePermissionEnum::CRM_ORDER_TRACKING->value,
    ];
}

test('ownership coverage outranks load inside the ownership pool', function () {
    $store = ownerStore();
    $heavyOwner = ownerMember($store, confirmPermissions());
    $lightOwner = ownerMember($store, confirmPermissions());
    ownerShift($store, $heavyOwner);
    ownerShift($store, $lightOwner);

    $p1 = ownerProduct($store, 'Owned Laptop');
    $p2 = ownerProduct($store, 'Owned Mouse');

    ownsProduct($store, $heavyOwner, $p1);
    ownsProduct($store, $heavyOwner, $p2);
    ownsProduct($store, $lightOwner, $p2);

    // The heavy owner already carries two open orders, the light one none —
    // coverage (2 of 2 vs 1 of 2) must still decide.
    ownerClaim(ownerOrder($store, $p1), $heavyOwner);
    ownerClaim(ownerOrder($store, $p2), $heavyOwner);

    $order = ownerOrder($store, $p1, $p2);
    app(OrderAssignmentService::class)->assign($order);

    expect($order->fresh()->assigned_to_membership_id)->toBe($heavyOwner->id);
});

test('equal coverage falls back to the load balancer', function () {
    $store = ownerStore();
    $loaded = ownerMember($store, confirmPermissions());
    $free = ownerMember($store, confirmPermissions());
    ownerShift($store, $loaded);
    ownerShift($store, $free);

    $p1 = ownerProduct($store, 'Shared Phone');
    $p2 = ownerProduct($store, 'Shared Case');

    ownsProduct($store, $loaded, $p1);
    ownsProduct($store, $free, $p2);

    ownerClaim(ownerOrder($store, $p1), $loaded);

    $order = ownerOrder($store, $p1, $p2);
    app(OrderAssignmentService::class)->assign($order);

    expect($order->fresh()->assigned_to_membership_id)->toBe($free->id);
});

test('a capped owner falls through to the general pool when overflow is off', function () {
    $store = ownerStore();
    $capped = ownerMember($store, confirmPermissions());
    $general = ownerMember($store, confirmPermissions());
    ownerShift($store, $capped, 'confirm', 1);
    ownerShift($store, $general);

    $product = ownerProduct($store, 'Capped Ownership');
    ownsProduct($store, $capped, $product);

    // The owner sits at its strict cap of 1.
    ownerClaim(ownerOrder($store, $product), $capped);

    $order = ownerOrder($store, $product);
    app(OrderAssignmentService::class)->assign($order);

    expect($order->fresh()->assigned_to_membership_id)->toBe($general->id)
        ->and($order->fresh()->over_capacity)->toBeFalse();
});

test('overflow extends an owner inside the ownership pool before the general pool', function () {
    $store = ownerStore();
    $capped = ownerMember($store, confirmPermissions());
    $general = ownerMember($store, confirmPermissions());
    ownerShift($store, $capped, 'confirm', 1);
    ownerShift($store, $general);

    $product = ownerProduct($store, 'Headroom Ownership');
    ownsProduct($store, $capped, $product);

    ownerClaim(ownerOrder($store, $product), $capped);

    $store->settings()->updateOrCreate([], [
        'distribution_overflow_enabled' => true,
        'distribution_overflow_percentage' => 100,
    ]);

    $order = ownerOrder($store, $product);
    app(OrderAssignmentService::class)->assign($order);

    // The overloaded owner beats the idle general confirmer: overflow is
    // tried inside the ownership pool before any fallback.
    expect($order->fresh()->assigned_to_membership_id)->toBe($capped->id)
        ->and($order->fresh()->over_capacity)->toBeTrue();
});

test('ownership never bypasses the ORDER_CONFIRM permission', function () {
    $store = ownerStore();
    $ownerless = ownerMember($store, [StorePermissionEnum::ORDER_VIEW->value]);
    $general = ownerMember($store, confirmPermissions());
    ownerShift($store, $general);

    $p1 = ownerProduct($store, 'Forbidden Fruit A');
    $p2 = ownerProduct($store, 'Forbidden Fruit B');

    ownsProduct($store, $ownerless, $p1);
    ownsProduct($store, $ownerless, $p2);

    $order = ownerOrder($store, $p1, $p2);
    app(OrderAssignmentService::class)->assign($order);

    expect($order->fresh()->assigned_to_membership_id)->toBe($general->id);
});

test('tracking prefers the tracker owning more of the order products over lighter load', function () {
    $store = ownerStore();
    $owner = ownerMember($store, trackPermissions());
    $general = ownerMember($store, trackPermissions());
    ownerShift($store, $owner, 'track');
    ownerShift($store, $general, 'track');

    $p1 = ownerProduct($store, 'Tracked Router');
    $p2 = ownerProduct($store, 'Tracked Cable');

    ownsProduct($store, $owner, $p1);
    ownsProduct($store, $owner, $p2);
    ownsProduct($store, $general, $p2);

    // The full-coverage owner already carries one open tracking, the
    // partial one none — coverage (2 of 2 vs 1 of 2) must still decide.
    $order = ownerOrder($store, $p1, $p2);
    $existing = OrderTracking::create([
        'store_id' => $store->id,
        'order_id' => $order->id,
        'tracking_status' => OrderTrackingStatus::SHIPPED->value,
        'shipped_at' => now(),
    ]);
    $existing->update([
        'assigned_to_membership_id' => $owner->id,
        'assigned_at' => now()->subMinutes(5),
        'assignment_method' => 'auto',
    ]);

    $tracking = OrderTracking::create([
        'store_id' => $store->id,
        'order_id' => $order->id,
        'tracking_status' => OrderTrackingStatus::SHIPPED->value,
        'shipped_at' => now(),
    ]);

    app(OrderTrackingAssignmentService::class)->assign($tracking);

    expect($tracking->fresh()->assigned_to_membership_id)->toBe($owner->id);
});

test('tracking falls back to the general pool when the owner has no track shift', function () {
    $store = ownerStore();
    $offShiftOwner = ownerMember($store, trackPermissions());
    $general = ownerMember($store, trackPermissions());
    ownerShift($store, $general, 'track');

    $p1 = ownerProduct($store, 'Unshifted Modem');
    $p2 = ownerProduct($store, 'Unshifted Antenna');

    ownsProduct($store, $offShiftOwner, $p1);
    ownsProduct($store, $offShiftOwner, $p2);

    $order = ownerOrder($store, $p1, $p2);
    $tracking = OrderTracking::create([
        'store_id' => $store->id,
        'order_id' => $order->id,
        'tracking_status' => OrderTrackingStatus::SHIPPED->value,
        'shipped_at' => now(),
    ]);

    app(OrderTrackingAssignmentService::class)->assign($tracking);

    expect($tracking->fresh()->assigned_to_membership_id)->toBe($general->id);
});

test('ownership resolves in one query regardless of how many owners exist', function () {
    $store = ownerStore();
    $first = ownerMember($store, confirmPermissions());
    $second = ownerMember($store, confirmPermissions());
    $third = ownerMember($store, confirmPermissions());
    ownerShift($store, $first);
    ownerShift($store, $second);
    ownerShift($store, $third);

    $p1 = ownerProduct($store, 'Counted One');
    $p2 = ownerProduct($store, 'Counted Two');

    ownsProduct($store, $first, $p1);

    $sqls = [];
    DB::listen(function ($query) use (&$sqls): void {
        if (str_contains($query->sql, 'confirmation_product_assignments') && str_starts_with(ltrim($query->sql), 'select')) {
            $sqls[] = $query->sql;
        }
    });

    app(OrderAssignmentService::class)->assign(ownerOrder($store, $p1, $p2));
    $withOneOwner = count($sqls);

    $sqls = [];
    ownsProduct($store, $second, $p1);
    ownsProduct($store, $second, $p2);
    ownsProduct($store, $third, $p2);

    app(OrderAssignmentService::class)->assign(ownerOrder($store, $p1, $p2));
    $withThreeOwners = count($sqls);

    expect($withOneOwner)->toBe(1)
        ->and($withThreeOwners)->toBe(1);
});

test('no retroactive reshuffle: handover keeps a still-on-shift assignee despite better ownership arriving', function () {
    $store = ownerStore();
    $first = ownerMember($store, confirmPermissions());
    $better = ownerMember($store, confirmPermissions());
    ownerShift($store, $first);
    ownerShift($store, $better);

    $p1 = ownerProduct($store, 'Stable Product');
    $p2 = ownerProduct($store, 'Late Product');

    ownsProduct($store, $first, $p1);

    $order = ownerOrder($store, $p1, $p2);
    app(OrderAssignmentService::class)->assign($order);
    expect($order->fresh()->assigned_to_membership_id)->toBe($first->id);

    // A higher-coverage owner appears while the assignee is still on shift:
    // the handover sweep must not reshuffle.
    ownsProduct($store, $better, $p1);
    ownsProduct($store, $better, $p2);

    app(OrderAssignmentService::class)->handleShiftHandover($store);
    expect($order->fresh()->assigned_to_membership_id)->toBe($first->id);

    // Once the assignee's shift ends, the sweep re-runs selection and the
    // higher-coverage owner wins.
    ConfirmationShift::where('membership_id', $first->id)->update(['is_active' => false]);

    app(OrderAssignmentService::class)->handleShiftHandover($store);
    expect($order->fresh()->assigned_to_membership_id)->toBe($better->id);
});
