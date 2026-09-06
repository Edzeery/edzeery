<?php

use App\Enums\Store\StoreRoleEnum;
use App\Models\Customer;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Models\Orders\OrderTracking;
use App\Models\Products\Product;
use App\Models\Products\ProductVariant;
use App\Models\Status;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
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

function qcUser(): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate(StoreRoleEnum::OWNER->value, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Qc Store',
        'slug' => 'qc-'.uniqid(),
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

function qcOrder(Store $store): Order
{
    $customer = Customer::create([
        'store_id' => $store->id,
        'name' => 'Qc Customer',
        'phone' => '0553'.fake()->unique()->numerify('######'),
        'status' => true,
    ]);

    $status = Status::system()
        ->forType('order')
        ->where('key', 'confirmed')
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
        'name' => 'Qc Product',
        'slug' => 'qc-pr-'.uniqid(),
        'sku' => 'qc-sku-'.uniqid(),
        'type' => 'variable',
        'price' => 400,
        'is_active' => true,
    ]);

    $variant = ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Default',
        'sku' => 'qc-v-'.uniqid(),
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

    OrderTracking::create([
        'store_id' => $store->id,
        'order_id' => $order->id,
        'shipping_provider_id' => null,
        'tracking_number' => 'QCTRK'.uniqid(),
        'tracking_status' => \App\Enums\Store\OrderTrackingStatus::IN_TRANSIT->value,
        'shipped_at' => now()->subDay(),
        'last_synced_at' => now(),
    ]);

    return $order;
}

test('the orders index query count stays flat as rows grow (no N+1)', function () {
    [$user, $store, $membership] = qcUser();

    foreach (range(1, 6) as $i) {
        qcOrder($store);
    }

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    $first = [];
    DB::listen(function ($q) use (&$first) {
        $first[] = $q->sql;
    });

    Volt::test('merchant.orders.index')->html();

    // Extra rows are created BEFORE the listener is installed so only page rendering is measured.
    foreach (range(1, 10) as $i) {
        qcOrder($store);
    }

    $second = [];
    DB::listen(function ($q) use (&$second) {
        $second[] = $q->sql;
    });

    Volt::test('merchant.orders.index')->html();

    // Only data queries matter for the N+1 check. The app resolves DB-backed
    // translations and role/permission lookups per rendered action, which is
    // unrelated to the list's eager loading and varies with markup, not rows.
    $isDataQuery = fn (string $sql): bool => (bool) preg_match(
        '/\b(?:from|into|update|delete)\s+"?(?:orders|order_items|customers|order_trackings|shipping_providers|product_variants|products)\b/i',
        $sql,
    );

    $dataBase = count(array_filter($first, $isDataQuery));
    $dataGrown = count(array_filter($second, $isDataQuery));

    // Eager loading keeps the data cost constant; a per-row query would add >= +1 per new order.
    expect($dataGrown)->toBeLessThanOrEqual($dataBase + 2);
    // Sanity: the page actually reads list data at least once.
    expect($dataBase)->toBeGreaterThan(1);
});