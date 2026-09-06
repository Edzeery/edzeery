<?php

use App\Enums\Store\OrderTrackingStatus;
use App\Enums\Store\StoreRoleEnum;
use App\Models\Customer;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Models\Orders\OrderTracking;
use App\Models\Orders\OrderTrackingHistory;
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

function tphUser(): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate(StoreRoleEnum::OWNER->value, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Tph Store',
        'slug' => 'tph-'.uniqid(),
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

function tphOrder(Store $store, StoreMembership $membership): Order
{
    $customer = Customer::create([
        'store_id' => $store->id,
        'name' => 'Tph Customer',
        'phone' => '0552'.fake()->unique()->numerify('######'),
        'status' => true,
    ]);

    $status = Status::system()
        ->forType('order')
        ->where('key', 'in_transit')
        ->firstOrFail();

    $order = Order::create([
        'store_id' => $store->id,
        'customer_id' => $customer->id,
        'status_id' => $status->id,
        'number' => (new Order(['store_id' => $store->id]))->nextOrderNumber(),
        'total_amount' => 400,
        'shipping_cost' => 0,
    ]);

    $product = Product::create([
        'store_id' => $store->id,
        'name' => 'Tph Product',
        'slug' => 'tph-pr-'.uniqid(),
        'sku' => 'tph-sku-'.uniqid(),
        'type' => 'variable',
        'price' => 400,
        'is_active' => true,
    ]);

    $variant = ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Default',
        'sku' => 'tph-v-'.uniqid(),
        'price' => 400,
        'stock' => 10,
        'is_active' => true,
    ]);

    OrderItem::create([
        'store_id' => $store->id,
        'order_id' => $order->id,
        'product_id' => null,
        'product_variant_id' => $variant->id,
        'quantity' => 1,
        'price' => 400,
        'subtotal' => 400,
    ]);

    $tracking = OrderTracking::create([
        'store_id' => $store->id,
        'order_id' => $order->id,
        'shipping_provider_id' => null,
        'tracking_number' => 'TPHTRK'.uniqid(),
        'tracking_status' => OrderTrackingStatus::IN_TRANSIT->value,
        'shipped_at' => now()->subDays(2),
        'last_synced_at' => now(),
    ]);

    OrderTrackingHistory::create([
        'store_id' => $store->id,
        'order_id' => $order->id,
        'order_tracking_id' => $tracking->id,
        'status' => OrderTrackingStatus::SHIPPED->value,
        'changed_by_membership_id' => $membership->id,
        'notes' => 'Handed to the carrier',
        'created_at' => now()->subDay(),
    ]);

    OrderTrackingHistory::create([
        'store_id' => $store->id,
        'order_id' => $order->id,
        'order_tracking_id' => $tracking->id,
        'status' => OrderTrackingStatus::IN_TRANSIT->value,
        'changed_by_membership_id' => $membership->id,
        'notes' => 'Left the hub',
        'created_at' => now(),
    ]);

    return $order;
}

test('clicking the tracking-status badge opens the tracking-history popup (latest first)', function () {
    [$user, $store, $membership] = tphUser();
    $order = tphOrder($store, $membership);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    $component = Volt::test('merchant.tracking.index')
        ->call('openStatusHistory', $order->id)
        ->assertSet('statusHistoryFor', $order->id)
        ->assertSee(__('order_flow.tracking_history'))
        ->assertSee('Left the hub')
        ->assertSee('Handed to the carrier');

    $histories = $component->get('statusHistory');
    expect($histories)->not->toBeEmpty()
        ->and($histories[0]['status'])->toBe(OrderTrackingStatus::IN_TRANSIT->value)
        ->and($histories[1]['status'])->toBe(OrderTrackingStatus::SHIPPED->value);

    $meta = $component->get('statusHistoryMeta');
    expect($meta['number'])->toBe($order->number);
});

test('closing the tracking-history popup clears its state', function () {
    [$user, $store, $membership] = tphUser();
    $order = tphOrder($store, $membership);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.tracking.index')
        ->call('openStatusHistory', $order->id)
        ->call('closeStatusHistory')
        ->assertSet('statusHistoryFor', null)
        ->assertSet('statusHistory', [])
        ->assertSet('statusHistoryMeta', null);
});

test('the badge and the popup are wired as buttons (no tracking data, no popup state)', function () {
    [$user, $store, $membership] = tphUser();
    $order = tphOrder($store, $membership);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    $html = Volt::test('merchant.tracking.index')->html();

    expect($html)->toContain("openStatusHistory('{$order->id}')")
        ->and($html)->toContain(__('order_flow.tracking_history'));
});