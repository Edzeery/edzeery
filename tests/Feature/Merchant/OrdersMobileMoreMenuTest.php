<?php

use App\Enums\Store\StoreRoleEnum;
use App\Models\Customer;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
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

function moreUser(string $storeRole): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate($storeRole, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'More Store',
        'slug' => 'more-'.uniqid(),
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

function moreOrder(Store $store): Order
{
    $customer = Customer::create([
        'store_id' => $store->id,
        'name' => 'More Customer',
        'phone' => '0554'.fake()->unique()->numerify('######'),
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
        'name' => 'More Product',
        'slug' => 'more-pr-'.uniqid(),
        'sku' => 'more-sku-'.uniqid(),
        'type' => 'variable',
        'price' => 400,
        'is_active' => true,
    ]);

    $variant = ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Default',
        'sku' => 'more-v-'.uniqid(),
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

    return $order;
}

test('the mobile card ships a more-popover with 44px touch rows for a manager', function () {
    [$user, $store, $membership] = moreUser(StoreRoleEnum::OWNER->value);
    moreOrder($store);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    $html = Volt::test('merchant.orders.index')->html();

    expect($html)->toContain('orderMoreMenu')
        ->and($html)->toContain('min-h-[44px]')
        ->and($html)->toContain(__('merchant_panel.reassign'))
        ->and($html)->toContain(__('merchant_panel.edit'))
        ->and($html)->toContain(__('merchant.delete_permanently'))
        ->and($html)->toContain(__('general.more'));
});

test('the mobile more-popover renders no 44px rows for staff (manage/delete gated)', function () {
    [$user, $store, $membership] = moreUser(StoreRoleEnum::STAFF->value);
    moreOrder($store);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    $html = Volt::test('merchant.orders.index')->html();

    expect($html)->toContain('orderMoreMenu')
        // Rows carry the touch-target class only when a permitted action exists.
        ->and($html)->not->toContain('min-h-[44px]');
});