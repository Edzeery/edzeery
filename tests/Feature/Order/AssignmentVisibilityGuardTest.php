<?php

use App\Domains\Order\Models\ConfirmationShift;
use App\Domains\Order\Services\OrderAssignmentService;
use App\Domains\Order\Services\OrderTrackingAssignmentService;
use App\Domains\Order\Support\AssignmentCandidateResolver;
use App\Enums\Store\OrderTrackingStatus;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Models\Orders\OrderTracking;
use App\Models\Products\Product;
use App\Models\Products\ProductVariant;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Models\User;
use App\Services\Stores\StoreProductScopeService;
use Illuminate\Support\Facades\DB;

/**
 * Root-cause regression: specialist routing and visibility scoping shared one
 * table, so an order could be routed to a manager whose product scope made it
 * invisible to them ("assign-then-invisible"). The guard is an EXTRA exclusion
 * on the candidate list — specialist tiering itself is untouched.
 */
uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function (): void {
    config(['app.timezone' => 'Africa/Algiers']);
    \Illuminate\Support\Carbon::setTestNow(\Illuminate\Support\Carbon::create(2026, 5, 4, 10, 0, 0, 'Africa/Algiers'));
});

afterEach(function (): void {
    \Illuminate\Support\Carbon::setTestNow();
});

function guardStore(): Store
{
    $store = Store::create([
        'user_id' => User::factory()->create()->id,
        'name' => 'Guard Store',
        'slug' => 'guard-'.uniqid(),
        'status' => 'active', 'landing_template' => 'catalog',
        'landing_template' => 'catalog',
    ]);

    test()->withSession(['current_store_id' => $store->id]);
    test()->artisan('db:seed', ['--class' => \Database\Seeders\SystemStatusesSeeder::class, '--force' => true]);

    return $store;
}

function guardMembership(Store $store, string $role, array $permissions, string $roleScope = 'confirm'): StoreMembership
{
    $membership = StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => User::factory()->create()->id,
        'invited_by' => $store->user_id,
        'is_active' => true,
        'role' => $role,
    ]);

    $membership->syncPermissions($permissions);

    ConfirmationShift::create([
        'store_id' => $store->id,
        'membership_id' => $membership->id,
        'shift_type' => 'morning',
        'start_time' => '08:00',
        'end_time' => '17:00',
        'days_of_week' => [1, 2, 3, 4, 5],
        'is_active' => true,
        'role_scope' => $roleScope,
    ]);

    return $membership;
}

/** A manager with the role-scope permission + TEAM_VIEW_OWN, scoped to $scopedProduct. */
function guardScopedManager(Store $store, Product $scopedProduct, string $roleScope = 'confirm'): StoreMembership
{
    $rolePermission = $roleScope === 'track'
        ? StorePermissionEnum::CRM_ORDER_TRACKING->value
        : StorePermissionEnum::ORDER_CONFIRM->value;

    $manager = guardMembership($store, 'manager', [
        StorePermissionEnum::TEAM_VIEW_OWN->value,
        $rolePermission,
    ], $roleScope);

    app(StoreProductScopeService::class)->assign($store, $manager, $scopedProduct);

    return $manager;
}

function guardProduct(Store $store, string $name): Product
{
    return Product::create([
        'store_id' => $store->id,
        'name' => $name,
        'slug' => str($name)->slug().'-'.uniqid(),
        'sku' => 'G-'.strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 4)).'-'.strtoupper(uniqid()),
        'type' => 'simple',
        'price' => 500,
        'is_active' => true,
    ]);
}

function guardOrder(Store $store, array $products): Order
{
    $status = \App\Models\Status::system()->forType('order')->where('key', 'pending')->first();

    $order = Order::create([
        'store_id' => $store->id,
        'status_id' => $status?->id,
        'number' => (new Order(['store_id' => $store->id]))->nextOrderNumber(),
        'total_amount' => 500,
        'delivery_type' => 'home',
        'payment_method' => 'cod',
    ]);

    foreach ($products as $product) {
        $variant = ProductVariant::create([
            'store_id' => $store->id,
            'product_id' => $product->id,
            'name' => 'Default',
            'sku' => 'GV-'.strtoupper(uniqid()),
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

function guardTracking(Store $store, Order $order): OrderTracking
{
    return OrderTracking::create([
        'store_id' => $store->id,
        'order_id' => $order->id,
        'tracking_status' => OrderTrackingStatus::SHIPPED->value,
        'shipped_at' => now(),
    ]);
}

it('never auto-assigns an order whose products all fall outside a manager scope', function () {
    $store = guardStore();
    $scoped = guardProduct($store, 'Scoped');
    $other = guardProduct($store, 'Unrelated');

    $manager = guardScopedManager($store, $scoped);
    $general = guardMembership($store, 'staff', [StorePermissionEnum::ORDER_CONFIRM->value]);

    $order = guardOrder($store, [$other]);

    app(OrderAssignmentService::class)->assign($order);

    // The scoped manager is skipped; the unrestricted confirmer still wins.
    expect($order->fresh()->assigned_to_membership_id)->toBe($general->id)
        ->and($order->fresh()->assigned_to_membership_id)->not->toBe($manager->id);
});

it('leaves the order unassigned when the only candidate is scoped away', function () {
    $store = guardStore();
    $scoped = guardProduct($store, 'Scoped');
    $other = guardProduct($store, 'Unrelated');

    guardScopedManager($store, $scoped);

    $order = guardOrder($store, [$other]);

    app(OrderAssignmentService::class)->assign($order);

    expect($order->fresh()->assigned_to_membership_id)->toBeNull();
});

it('keeps the manager eligible as soon as one ordered product is in scope', function () {
    $store = guardStore();
    $scoped = guardProduct($store, 'Scoped');
    $other = guardProduct($store, 'Unrelated');

    $manager = guardScopedManager($store, $scoped);

    // Any-match (not all-match): the order also carries an unrelated product.
    $order = guardOrder($store, [$scoped, $other]);

    app(OrderAssignmentService::class)->assign($order);

    expect($order->fresh()->assigned_to_membership_id)->toBe($manager->id);
});

it('hides the scoped manager from the confirm reassign candidate list only while out of scope', function () {
    $store = guardStore();
    $scoped = guardProduct($store, 'Scoped');
    $other = guardProduct($store, 'Unrelated');

    $manager = guardScopedManager($store, $scoped);
    $general = guardMembership($store, 'staff', [StorePermissionEnum::ORDER_CONFIRM->value]);

    $resolver = app(AssignmentCandidateResolver::class);
    $permission = StorePermissionEnum::ORDER_CONFIRM->value;

    $outOfScope = $resolver->resolve($store->id, 'confirm', $permission, [$other->id])->pluck('id')->all();
    $inScope = $resolver->resolve($store->id, 'confirm', $permission, [$scoped->id])->pluck('id')->all();

    expect($outOfScope)->toBe([(string) $general->id])->not->toContain((string) $manager->id)
        ->and($inScope)->toContain((string) $manager->id, (string) $general->id);
});

it('applies the same guard to tracking auto-assignment', function () {
    $store = guardStore();
    $scoped = guardProduct($store, 'Scoped');
    $other = guardProduct($store, 'Unrelated');

    guardScopedManager($store, $scoped, 'track');

    $tracking = guardTracking($store, guardOrder($store, [$other]));

    app(OrderTrackingAssignmentService::class)->assign($tracking);

    expect($tracking->fresh()->assigned_to_membership_id)->toBeNull();
});

it('issues a constant number of scope queries for any candidate count', function () {
    $scopeQueries = function (Store $store): int {
        DB::connection()->flushQueryLog();
        DB::connection()->enableQueryLog();

        app(OrderAssignmentService::class)->assign(guardOrder($store, [guardProduct($store, 'Any')]));

        return collect(DB::getQueryLog())
            ->filter(fn (array $query) => str_contains($query['query'], 'membership_product_scopes'))
            ->count();
    };

    $single = guardStore();
    guardScopedManager($single, guardProduct($single, 'Scoped'));

    $many = guardStore();
    foreach (range(1, 4) as $index) {
        guardScopedManager($many, guardProduct($many, "Scoped {$index}"));
    }

    $one = $scopeQueries($single);
    $four = $scopeQueries($many);

    // Exactly one batched scope read regardless of how many candidates there
    // are — a per-candidate guard would scale with the candidate count.
    expect($one)->toBe(1)
        ->and($four)->toBe($one);
});
