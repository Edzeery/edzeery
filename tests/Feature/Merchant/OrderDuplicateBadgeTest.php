<?php

use App\Models\Customer;
use App\Models\Orders\Order;
use App\Models\Products\Product;
use App\Models\Products\ProductVariant;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use Illuminate\Support\Carbon;
use Livewire\Volt\Volt;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\StoreRolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\SystemStatusesSeeder::class);
});

function dbbUser(string $storeRole = 'owner'): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate($storeRole, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Dup Badge Store',
        'slug' => 'dupb-'.uniqid(),
        'status' => 'active',
    ]);

    StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'invited_by' => $user->id,
        'is_active' => true,
        'role' => $storeRole,
    ]);

    return [$user, $store];
}

function dbbProduct(Store $store, string $seed): array
{
    $product = Product::create([
        'store_id' => $store->id,
        'name' => 'Dup Badge '.$seed,
        'slug' => 'dupb-pr-'.$seed.'-'.uniqid(),
        'sku' => 'dupb-sku-'.$seed.'-'.uniqid(),
        'type' => 'variable',
        'price' => 400,
        'is_active' => true,
    ]);

    $variant = ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Default',
        'sku' => 'dupb-v-'.$seed.'-'.uniqid(),
        'price' => 400,
        'stock' => 50,
        'is_active' => true,
    ]);

    return [$product, $variant];
}

function dbbOrder(Store $store, string $phone, string $statusKey = 'confirmed', ?Carbon $createdAt = null): Order
{
    $customer = Customer::firstOrCreate(
        ['store_id' => $store->id, 'phone' => $phone],
        ['name' => 'Dup Badge Customer', 'status' => true],
    );

    $status = \App\Models\Status::system()
        ->forType('order')
        ->where('key', $statusKey)
        ->firstOrFail();

    $order = Order::create([
        'store_id' => $store->id,
        'customer_id' => $customer->id,
        'status_id' => $status->id,
        'number' => (new Order(['store_id' => $store->id]))->nextOrderNumber(),
        'total_amount' => 400,
        'shipping_cost' => 0,
    ]);

    $order->created_at = $createdAt ?? now();
    $order->save();

    return $order;
}

function dbbAttachItem(Order $order, ProductVariant $variant, int $qty = 1): void
{
    $order->items()->create([
        'store_id' => $order->store_id,
        'product_variant_id' => $variant->id,
        'product_id' => $variant->product_id,
        'quantity' => $qty,
        'price' => $variant->price,
        'subtotal' => $variant->price * $qty,
    ]);
}

function dbbVolt(array $userStore)
{
    [$user, $store] = $userStore;
    actingAs($user)->withSession(['current_store_id' => $store->id]);

    return Volt::test('merchant.orders.index');
}

// The pill tones are the unique page signals: danger = duplicate, warning = probable,
// neutral = repeat. (Asserting translated labels collides with status-kit labels such as
// "Duplicate" for orders already carrying the duplicate status.)

test('same phone + same product in window: duplicate pill next to the customer name, popup classifies', function () {
    $us = dbbUser();
    $st = $us[1];
    [, $variantA] = dbbProduct($st, 'a');

    $orderA = dbbOrder($st, '0550666011');
    $orderB = dbbOrder($st, '0550666011');
    dbbAttachItem($orderA, $variantA);
    dbbAttachItem($orderB, $variantA);

    $component = dbbVolt($us)
        ->assertSee('#'.$orderA->number)
        ->assertSee('#'.$orderB->number)
        ->assertSee('edz-badge--danger')
        ->assertDontSee('edz-badge--warning');

    $component
        ->call('openDuplicateScan', $orderA->id);

    $results = $component->get('duplicateScanResults');
    expect($results)->toHaveCount(1);
    expect(collect($results)->pluck('order_id')->all())->toBe([$orderB->id]);

    $component
        ->assertSet('duplicateScanLevel', 'duplicate')
        ->assertSet('showDuplicateScanModal', true)
        ->assertSet('duplicateScanNumber', $orderA->number)
        ->assertSee(__('order_flow.duplicate_detected', ['count' => 1]))
        ->assertSee('#'.$orderB->number);
});

test('same phone but different products in window: probable pill and popup explains it', function () {
    $us = dbbUser();
    $st = $us[1];
    [, $variantA] = dbbProduct($st, 'a');
    [, $variantB] = dbbProduct($st, 'b');

    $orderA = dbbOrder($st, '0550666012');
    $orderB = dbbOrder($st, '0550666012');
    dbbAttachItem($orderA, $variantA);
    dbbAttachItem($orderB, $variantB);

    $component = dbbVolt($us)
        ->assertSee('edz-badge--warning')
        ->assertDontSee('edz-badge--danger')
        ->assertDontSee('edz-badge--neutral');

    $component
        ->call('openDuplicateScan', $orderA->id)
        ->assertSet('duplicateScanLevel', 'probable')
        ->assertSet('duplicateScanPhoneCount', 1)
        ->assertSet('duplicateScanResults', [])
        ->assertSee(__('order_flow.duplicate_phone_only_orders', ['count' => 1]));
});

test('prior order of the same phone that reached the carrier flags a repeat pill regardless of age', function () {
    $us = dbbUser();
    $st = $us[1];
    [, $variantA] = dbbProduct($st, 'a');

    $oldSent = dbbOrder($st, '0550666013', 'shipped', now()->subDays(60));
    dbbAttachItem($oldSent, $variantA);
    $fresh = dbbOrder($st, '0550666013');

    $component = dbbVolt($us)
        ->assertSee('edz-badge--neutral')
        ->assertDontSee('edz-badge--danger')
        ->assertDontSee('edz-badge--warning');

    $component
        ->call('openDuplicateScan', $fresh->id)
        ->assertSet('duplicateScanLevel', 'repeat')
        ->assertSet('duplicateScanRepeatCount', 1)
        ->assertSet('duplicateScanResults', [])
        ->assertSee(__('order_flow.dup_repeat_carrier_sent', ['count' => 1]));
});

test('duplicate status wins precedence and the popup still surfaces the repeat history', function () {
    $us = dbbUser();
    $st = $us[1];
    [, $variantA] = dbbProduct($st, 'a');

    $oldSent = dbbOrder($st, '0550666014', 'shipped', now()->subDays(70));
    dbbAttachItem($oldSent, $variantA);

    $orderA = dbbOrder($st, '0550666014');
    $orderB = dbbOrder($st, '0550666014');
    dbbAttachItem($orderA, $variantA);
    dbbAttachItem($orderB, $variantA);

    $component = dbbVolt($us)
        ->assertSee('edz-badge--danger')
        ->assertDontSee('edz-badge--neutral');

    $component
        ->call('openDuplicateScan', $orderA->id)
        ->assertSet('duplicateScanLevel', 'duplicate')
        ->assertSet('duplicateScanRepeatCount', 1)
        ->assertSee(__('order_flow.dup_repeat_carrier_sent', ['count' => 1]));
});

test('a sent order does not flag itself as repeat when it has no siblings', function () {
    $us = dbbUser();
    $st = $us[1];
    dbbOrder($st, '0550666015', 'shipped');

    $component = dbbVolt($us)
        ->assertDontSee('edz-badge--danger')
        ->assertDontSee('edz-badge--warning')
        ->assertDontSee('edz-badge--neutral');

    $component
        ->call('openDuplicateScan', Order::where('store_id', $st->id)->first()->id)
        ->assertSet('duplicateScanLevel', 'none')
        ->assertSee(__('order_flow.no_duplicates'));
});

test('orders outside the 30-day window with plain status render no pill at all', function () {
    $us = dbbUser();
    $st = $us[1];
    [, $variantA] = dbbProduct($st, 'a');

    $orderA = dbbOrder($st, '0550666016', 'confirmed', now()->subDays(40));
    $orderB = dbbOrder($st, '0550666016', 'confirmed', now()->subDays(35));
    dbbAttachItem($orderA, $variantA);
    dbbAttachItem($orderB, $variantA);

    dbbVolt($us)
        ->assertDontSee('edz-badge--danger')
        ->assertDontSee('edz-badge--warning')
        ->assertDontSee('edz-badge--neutral');
});

test('orders already marked as duplicate status never show any pill', function () {
    $us = dbbUser();
    $st = $us[1];
    [, $variantA] = dbbProduct($st, 'a');

    $orderA = dbbOrder($st, '0550666017', 'duplicate');
    $orderB = dbbOrder($st, '0550666017', 'duplicate');
    dbbAttachItem($orderA, $variantA);
    dbbAttachItem($orderB, $variantA);

    dbbVolt($us)
        ->assertDontSee('edz-badge--danger')
        ->assertDontSee('edz-badge--warning')
        ->assertDontSee('edz-badge--neutral');
});

test('soft-deleted siblings are excluded from detection', function () {
    $us = dbbUser();
    $st = $us[1];
    [, $variantA] = dbbProduct($st, 'a');

    $orderA = dbbOrder($st, '0550666018');
    $orderB = dbbOrder($st, '0550666018');
    dbbAttachItem($orderA, $variantA);
    dbbAttachItem($orderB, $variantA);

    $orderB->delete();

    dbbVolt($us)
        ->assertDontSee('edz-badge--danger')
        ->assertDontSee('edz-badge--warning')
        ->assertDontSee('edz-badge--neutral');
});

test('the count is capped at 9+ when many duplicates share the phone and product', function () {
    $us = dbbUser();
    $st = $us[1];
    [, $variantA] = dbbProduct($st, 'a');

    for ($i = 0; $i < 12; $i++) {
        $order = dbbOrder($st, '0550666019');
        dbbAttachItem($order, $variantA);
    }

    dbbVolt($us)
        ->assertSee('edz-badge--danger')
        ->assertSee('×9+');
});

test('opening order details from a duplicate-scan row closes the scan popup', function () {
    $us = dbbUser();
    $st = $us[1];
    [, $variantA] = dbbProduct($st, 'a');

    $orderA = dbbOrder($st, '0550666020');
    $orderB = dbbOrder($st, '0550666020');
    dbbAttachItem($orderA, $variantA);
    dbbAttachItem($orderB, $variantA);

    dbbVolt($us)
        ->call('openDuplicateScan', $orderA->id)
        ->assertSet('showDuplicateScanModal', true)
        ->call('openOrderDetails', $orderB->id)
        ->assertSet('showDuplicateScanModal', false)
        ->assertSet('detailsOrderId', $orderB->id);
});