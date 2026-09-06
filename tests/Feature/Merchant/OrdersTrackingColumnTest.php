<?php

use App\Domains\Order\Models\UserColumnPreference;
use App\Enums\Store\OrderTrackingStatus;
use App\Enums\Store\StoreRoleEnum;
use Edzeery\MyStatusKit\Facades\Status as StoreStatus;
use App\Models\Customer;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Models\Orders\OrderTracking;
use App\Models\Products\Product;
use App\Models\Products\ProductVariant;
use App\Models\Status;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use Livewire\Volt\Volt;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\StoreRolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\SystemStatusesSeeder::class);
});

// ---------------------------------------------------------------------------
// Self-contained fixtures (each Pest file owns its helper functions).
// ---------------------------------------------------------------------------

function tscUser(string $storeRole = 'owner'): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate($storeRole, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Tsc Store',
        'slug' => 'tsc-' . uniqid(),
        'status' => 'active',
    ]);

    $membership = StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'invited_by' => $user->id,
        'is_active' => true,
        'role' => $storeRole,
    ]);

    return [$user, $store, $membership];
}

function tscVariant(Store $store): ProductVariant
{
    $product = Product::create([
        'store_id' => $store->id,
        'name' => 'Tsc Product',
        'slug' => 'tsc-pr-' . uniqid(),
        'sku' => 'tsc-sku-' . uniqid(),
        'type' => 'variable',
        'price' => 400,
        'is_active' => true,
    ]);

    return ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Default',
        'sku' => 'tsc-v-' . uniqid(),
        'price' => 400,
        'stock' => 10,
        'is_active' => true,
    ]);
}

function tscOrder(Store $store, bool $withTracking, string $trackingStatus = 'in_transit'): Order
{
    $status = Status::system()
        ->forType('order')
        ->where('key', 'preparing')
        ->firstOrFail();

    $order = Order::create([
        'store_id' => $store->id,
        'customer_id' => null,
        'status_id' => $status->id,
        'number' => (new Order(['store_id' => $store->id]))->nextOrderNumber(),
        'total_amount' => 400,
        'shipping_cost' => 0,
    ]);

    $customer = Customer::create([
        'store_id' => $store->id,
        'name' => 'Tsc Customer',
        'phone' => '0551' . fake()->unique()->numerify('######'),
        'status' => true,
    ]);
    $order->update(['customer_id' => $customer->id]);

    OrderItem::create([
        'store_id' => $store->id,
        'order_id' => $order->id,
        'product_variant_id' => tscVariant($store)->id,
        'product_id' => null,
        'quantity' => 1,
        'price' => 400,
        'subtotal' => 400,
    ]);

    if ($withTracking) {
        OrderTracking::create([
            'store_id' => $store->id,
            'order_id' => $order->id,
            'shipping_provider_id' => null,
            'tracking_number' => 'TSCTRK' . uniqid(),
            'tracking_status' => $trackingStatus,
            'shipped_at' => now(),
            'last_synced_at' => now(),
        ]);
    }

    return $order->fresh();
}

test('tracking_status and confirmed_by are no longer valid order-table preference columns', function () {
    [$user, $store, $membership] = tscUser(StoreRoleEnum::OWNER->value);
    $order = tscOrder($store, true, OrderTrackingStatus::DAMAGED->value);

    // A stale preference row (written before the columns were removed) must
    // be silently dropped instead of rendering unknown columns. prefs_version
    // matches current layout so the stored list is honoured (intersected
    // against the valid keys) rather than reset by a legacy migration.
    UserColumnPreference::create([
        'membership_id' => $membership->id,
        'view_key' => 'orders_index',
        'visible_columns' => ['tracking_status', 'confirmed_by', 'number'],
        'table_style' => 'default',
        'prefs_version' => 2,
    ]);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.orders.index')
        ->assertSet('visibleColumns', fn ($cols) => !in_array('tracking_status', $cols, true))
        ->assertSet('visibleColumns', fn ($cols) => !in_array('confirmed_by', $cols, true))
        ->assertSet('visibleColumns', fn ($cols) => in_array('number', $cols, true))
        ->assertSet('visibleColumns', fn ($cols) => count($cols) > 0)
        ->assertSee($order->number)
        ->assertDontSee(StoreStatus::for('tracking', OrderTrackingStatus::DAMAGED->value)->label());
});

test('the tracking badge stays hidden while the column is disabled (default view)', function () {
    [$user, $store, $membership] = tscUser(StoreRoleEnum::OWNER->value);
    $order = tscOrder($store, true, OrderTrackingStatus::DAMAGED->value);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.orders.index')
        ->assertSee($order->number)
        ->assertDontSee(StoreStatus::for('tracking', OrderTrackingStatus::DAMAGED->value)->label());
});

test('orders without a shipment render fine with the default columns (no tracking-status column)', function () {
    [$user, $store, $membership] = tscUser(StoreRoleEnum::OWNER->value);
    $order = tscOrder($store, false);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    $component = Volt::test('merchant.orders.index')
        ->assertSet('visibleColumns', fn ($cols) => !in_array('tracking_status', $cols, true))
        ->assertSet('visibleColumns', fn ($cols) => count($cols) > 0)
        ->assertSee($order->number)
        ->assertDontSee(StoreStatus::for('tracking', OrderTrackingStatus::DAMAGED->value)->label());

    $html = $component->html();
    expect($html)->not->toContain('tracking_status');
});

test('the tracking column badge on the orders page stays informational only (no tracking-history UI here)', function () {
    $ordersView = file_get_contents(base_path('resources/views/livewire/merchant/orders/index.blade.php'));

    // Tracking-history is owned by the tracking page, not the orders table.
    expect($ordersView)->not->toContain('openTrackingHistory');
    expect($ordersView)->not->toContain('trackingHistoryOrderId');
    expect($ordersView)->not->toContain('partials.tracking-history-timeline');
});

test('the tracking-history timeline lives in a shared partial owned by the tracking page', function () {
    $partial = base_path('resources/views/livewire/merchant/tracking/partials/tracking-history-timeline.blade.php');
    expect($partial)->toBeFile();

    $trackingView = file_get_contents(base_path('resources/views/livewire/merchant/tracking/index.blade.php'));
    $drawerView = file_get_contents(base_path('resources/views/livewire/merchant/tracking/partials/order-drawer.blade.php'));
    $ordersView = file_get_contents(base_path('resources/views/livewire/merchant/orders/index.blade.php'));

    expect($trackingView)->toContain('partials.order-drawer');
    expect($drawerView)->toContain('partials.tracking-history-timeline');

    // No duplicated inline timeline markup left in the tracking drawer.
    expect($trackingView)->not->toMatch('/tracking_history_empty/');
    expect($drawerView)->not->toMatch('/tracking_history_empty/');

    // The orders page renders its own audit-log timeline (Order-history) via the extracted
    // event-log popup partial; tracking rows never leak into the orders page.
    expect($ordersView)->toContain('partials.order-events-modal');
    $eventsModalView = file_get_contents(base_path('resources/views/livewire/merchant/orders/partials/order-events-modal.blade.php'));
    expect($eventsModalView)->toContain('partials.order-events-timeline');
    expect($ordersView)->not->toContain('partials.tracking-history-timeline');
});