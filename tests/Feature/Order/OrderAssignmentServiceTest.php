<?php

use App\Domains\Order\Models\ConfirmationProductAssignment;
use App\Domains\Order\Models\ConfirmationShift;
use App\Domains\Order\Services\OrderAssignmentService;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Locations\City;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use App\Models\Customer;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Models\Products\Product;
use App\Models\Products\ProductVariant;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function (): void {
    config(['app.timezone' => 'Africa/Algiers']);
    \Illuminate\Support\Carbon::setTestNow(\Illuminate\Support\Carbon::create(2026, 5, 4, 10, 0, 0, 'Africa/Algiers'));
});

afterEach(function (): void {
    \Illuminate\Support\Carbon::setTestNow();
});

function assignmentStore(): Store
{
    $owner = User::factory()->create();

    $store = Store::create([
        'user_id' => $owner->id,
        'name' => 'Assignment Store',
        'slug' => 'assign-'.uniqid(),
        'status' => 'active',
        'landing_template' => 'catalog',
    ]);

    test()->withSession(['current_store_id' => $store->id]);
    test()->artisan('db:seed', ['--class' => \Database\Seeders\SystemStatusesSeeder::class, '--force' => true]);

    return $store;
}

function assignmentMembership(Store $store, string $role): StoreMembership
{
    $user = User::factory()->create();

    $membership = StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'invited_by' => $store->user_id,
        'is_active' => true,
        'role' => $role,
    ]);

    $membership->syncPermissions([
        StorePermissionEnum::ORDER_VIEW->value,
        StorePermissionEnum::ORDER_CONFIRM->value,
    ]);

    return $membership;
}

function assignmentProduct(Store $store, string $name): Product
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

function assignOrder(
    Store $store,
    Product $product
): Order {
    test()->artisan('db:seed', ['--class' => \Database\Seeders\SystemStatusesSeeder::class, '--force' => true]);

    $country = Country::firstOrCreate(
        ['code' => 'AL'],
        ['name' => 'Asg Land', 'is_active' => true]
    );
    $state = State::firstOrCreate(
        ['state_code' => 'ASG-01'],
        [
            'country_id' => $country->id,
            'name' => 'Asg State',
            'is_active' => true,
            'is_cod_available' => true,
        ]
    );
    $city = City::firstOrCreate(
        ['name' => 'Asg City', 'state_id' => $state->id],
        ['post_code' => '0000', 'is_active' => true]
    );

    $customer = Customer::firstOrCreate(
        ['store_id' => $store->id, 'phone' => '0560000000'],
        ['name' => 'Assign Customer', 'status' => true]
    );

    $status = \App\Models\Status::system()->forType('order')->where('key', 'pending')->first();

    $order = Order::create([
        'store_id' => $store->id,
        'customer_id' => $customer->id,
        'status_id' => $status?->id,
        'number' => (new Order(['store_id' => $store->id]))->nextOrderNumber(),
        'total_amount' => 1000,
        'state_id' => $state->id,
        'city_id' => $city->id,
        'delivery_type' => 'home',
        'payment_method' => 'cod',
    ]);

    $variant = ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Default',
        'sku' => 'asg-v-'.uniqid(),
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

test('specialist on active shift is preferred over general confirmer', function () {
    $store = assignmentStore();
    $specialist = assignmentMembership($store, 'staff');
    $general = assignmentMembership($store, 'staff');
    $other = assignmentMembership($store, 'staff');

    ConfirmationShift::create([
        'store_id' => $store->id,
        'membership_id' => $specialist->id,
        'shift_type' => 'morning',
        'start_time' => '08:00',
        'end_time' => '17:00',
        'days_of_week' => [1, 2, 3, 4, 5],
        'is_active' => true,
    ]);
    ConfirmationShift::create([
        'store_id' => $store->id,
        'membership_id' => $general->id,
        'shift_type' => 'morning',
        'start_time' => '08:00',
        'end_time' => '17:00',
        'days_of_week' => [1, 2, 3, 4, 5],
        'is_active' => true,
    ]);

    // A specialist product mapped to `specialist`, unrelated product to `other`
    $specialistProduct = assignmentProduct($store, 'Special Phone');
    $otherProduct = assignmentProduct($store, 'Blue Bag');

    ConfirmationProductAssignment::create([
        'store_id' => $store->id,
        'membership_id' => $specialist->id,
        'product_id' => $specialistProduct->id,
    ]);
    ConfirmationProductAssignment::create([
        'store_id' => $store->id,
        'membership_id' => $other->id,
        'product_id' => $otherProduct->id,
    ]);

    // Monday 10:00 — everyone on shift
    $order = assignOrder($store, $specialistProduct);

    $service = app(OrderAssignmentService::class);
    $service->assign($order);

    expect($order->fresh()->assigned_to_membership_id)->toBe($specialist->id);
});

test('general confirmer is used when no specialist is on active shift', function () {
    $store = assignmentStore();
    $specialist = assignmentMembership($store, 'staff');
    $general = assignmentMembership($store, 'staff');

    // The specialist is NOT on shift (no shift row), general IS on shift.
    ConfirmationShift::create([
        'store_id' => $store->id,
        'membership_id' => $general->id,
        'shift_type' => 'morning',
        'start_time' => '08:00',
        'end_time' => '17:00',
        'days_of_week' => [1, 2, 3, 4, 5],
        'is_active' => true,
    ]);

    $specialistProduct = assignmentProduct($store, 'Special Phone');

    ConfirmationProductAssignment::create([
        'store_id' => $store->id,
        'membership_id' => $specialist->id,
        'product_id' => $specialistProduct->id,
    ]);

    $order = assignOrder($store, $specialistProduct);

    $service = app(OrderAssignmentService::class);
    $service->assign($order);

    expect($order->fresh()->assigned_to_membership_id)->toBe($general->id);
});

test('load balances between two general confirmers on shift', function () {
    $store = assignmentStore();
    $a = assignmentMembership($store, 'staff');
    $b = assignmentMembership($store, 'staff');

    ConfirmationShift::create([
        'store_id' => $store->id,
        'membership_id' => $a->id,
        'shift_type' => 'morning',
        'start_time' => '08:00',
        'end_time' => '17:00',
        'days_of_week' => [1, 2, 3, 4, 5],
        'is_active' => true,
    ]);
    ConfirmationShift::create([
        'store_id' => $store->id,
        'membership_id' => $b->id,
        'shift_type' => 'morning',
        'start_time' => '08:00',
        'end_time' => '17:00',
        'days_of_week' => [1, 2, 3, 4, 5],
        'is_active' => true,
    ]);

    $product = assignmentProduct($store, 'Plain T-Shirt');

    // a already has one open order assigned
    $orderA = assignOrder($store, $product);
    $orderA->update([
        'assigned_to_membership_id' => $a->id,
        'assigned_at' => now()->subMinutes(5),
        'assignment_method' => 'auto',
    ]);

    $orderB = assignOrder($store, $product);

    $service = app(OrderAssignmentService::class);
    $service->assign($orderB);

    expect($orderB->fresh()->assigned_to_membership_id)->toBe($b->id);
});

test('order stays unassigned when no confirmer is on shift', function () {
    $store = assignmentStore();
    assignmentMembership($store, 'staff');

    // No shift rows at all → nobody eligible.
    $product = assignmentProduct($store, 'Lonely Radio');

    $order = assignOrder($store, $product);

    $service = app(OrderAssignmentService::class);
    $service->assign($order);

    expect($order->fresh()->assigned_to_membership_id)->toBeNull();
});

test('member who reached max_concurrent_orders cap is skipped', function () {
    $store = assignmentStore();
    $a = assignmentMembership($store, 'staff');
    $b = assignmentMembership($store, 'staff');

    ConfirmationShift::create([
        'store_id' => $store->id,
        'membership_id' => $a->id,
        'shift_type' => 'morning',
        'start_time' => '08:00',
        'end_time' => '17:00',
        'days_of_week' => [1, 2, 3, 4, 5],
        'is_active' => true,
        'max_concurrent_orders' => 1,
    ]);
    ConfirmationShift::create([
        'store_id' => $store->id,
        'membership_id' => $b->id,
        'shift_type' => 'morning',
        'start_time' => '08:00',
        'end_time' => '17:00',
        'days_of_week' => [1, 2, 3, 4, 5],
        'is_active' => true,
    ]);

    $product = assignmentProduct($store, 'Capped Phone');

    // `a` already holds one open order → at its cap of 1.
    $orderA = assignOrder($store, $product);
    $orderA->update([
        'assigned_to_membership_id' => $a->id,
        'assigned_at' => now()->subMinutes(5),
        'assignment_method' => 'auto',
    ]);

    $orderB = assignOrder($store, $product);

    $service = app(OrderAssignmentService::class);
    $service->assign($orderB);

    expect($orderB->fresh()->assigned_to_membership_id)->toBe($b->id);
});

test('all capped members leave the order unassigned', function () {
    $store = assignmentStore();
    $a = assignmentMembership($store, 'staff');
    $b = assignmentMembership($store, 'staff');

    ConfirmationShift::create([
        'store_id' => $store->id,
        'membership_id' => $a->id,
        'shift_type' => 'morning',
        'start_time' => '08:00',
        'end_time' => '17:00',
        'days_of_week' => [1, 2, 3, 4, 5],
        'is_active' => true,
        'max_concurrent_orders' => 1,
    ]);
    ConfirmationShift::create([
        'store_id' => $store->id,
        'membership_id' => $b->id,
        'shift_type' => 'morning',
        'start_time' => '08:00',
        'end_time' => '17:00',
        'days_of_week' => [1, 2, 3, 4, 5],
        'is_active' => true,
        'max_concurrent_orders' => 1,
    ]);

    $product = assignmentProduct($store, 'Saturated Gadget');

    // Both already hold one open order each → both at cap 1.
    $orderA = assignOrder($store, $product);
    $orderA->update([
        'assigned_to_membership_id' => $a->id,
        'assigned_at' => now()->subMinutes(5),
        'assignment_method' => 'auto',
    ]);
    $orderB = assignOrder($store, $product);
    $orderB->update([
        'assigned_to_membership_id' => $b->id,
        'assigned_at' => now()->subMinutes(1),
        'assignment_method' => 'auto',
    ]);

    $orderC = assignOrder($store, $product);

    $service = app(OrderAssignmentService::class);
    $service->assign($orderC);

    expect($orderC->fresh()->assigned_to_membership_id)->toBeNull();
});