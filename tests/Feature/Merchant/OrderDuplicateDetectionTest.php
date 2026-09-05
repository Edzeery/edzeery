<?php

use App\Enums\Store\StoreRoleEnum;
use App\Models\Customer;
use App\Models\Orders\Order;
use App\Models\Products\Product;
use App\Models\Products\ProductVariant;
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

function dupUser(string $storeRole = 'owner'): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate($storeRole, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Dup Store',
        'slug' => 'dup-'.uniqid(),
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

function dupVariant(Store $store): array
{
    $product = Product::create([
        'store_id' => $store->id,
        'name' => 'Dup Product',
        'slug' => 'dup-pr-'.uniqid(),
        'sku' => 'dup-sku-'.uniqid(),
        'type' => 'variable',
        'price' => 400,
        'is_active' => true,
    ]);

    $variant = ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Default',
        'sku' => 'dup-v-'.uniqid(),
        'price' => 400,
        'stock' => 50,
        'is_active' => true,
    ]);

    return [$product, $variant];
}

function dupOrder(Store $store, string $phone, array $variantIds, string $statusKey = 'confirmed'): Order
{
    $customer = \App\Models\Customer::firstOrCreate(
        ['store_id' => $store->id, 'phone' => $phone],
        ['name' => 'Dup Customer', 'status' => true],
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

    foreach ($variantIds as $variantId) {
        $variant = ProductVariant::find($variantId);
        $order->items()->create([
            'store_id' => $store->id,
            'product_variant_id' => $variantId,
            'product_id' => $variant->product_id,
            'quantity' => 1,
            'price' => 400,
            'subtotal' => 400,
        ]);
    }

    return $order;
}

function dupVolt(array $userStore)
{
    [$user, $store] = $userStore;
    actingAs($user)->withSession(['current_store_id' => $store->id]);

    return Volt::test('merchant.orders.index');
}

test('findSimilar accepts an array candidate built from the unsaved form', function () {
    [$user, $store] = dupUser(StoreRoleEnum::OWNER->value);
    [$product, $variant] = dupVariant($store);
    $otherProduct = Product::create([
        'store_id' => $store->id,
        'name' => 'Other Product',
        'slug' => 'other-'.uniqid(),
        'sku' => 'other-'.uniqid(),
        'type' => 'variable',
        'price' => 200,
        'is_active' => true,
    ]);
    ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $otherProduct->id,
        'name' => 'Default',
        'sku' => 'other-v-'.uniqid(),
        'price' => 200,
        'stock' => 10,
        'is_active' => true,
    ]);

    dupOrder($store, '0550998877', [$variant->id], 'in_transit');

    $candidate = [
        'store_id' => $store->id,
        'exclude_id' => null,
        'customer_phone' => '0550998877',
        'items' => [[
            'product_variant_id' => $variant->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]],
    ];

    $results = app(\App\Domains\Order\Services\OrderDuplicateService::class)->findSimilar($candidate);

    expect($results)->toHaveCount(1)
        ->and($results[0]['total_overlap_qty'])->toBe(1)
        ->and($results[0]['status_key'])->toBe('in_transit');
});

test('findSimilar array candidate excludes the order being edited', function () {
    [$user, $store] = dupUser(StoreRoleEnum::OWNER->value);
    [$product, $variant] = dupVariant($store);

    $existing = dupOrder($store, '0550111222', [$variant->id], 'confirmed');

    $candidate = [
        'store_id' => $store->id,
        'exclude_id' => $existing->id,
        'customer_phone' => '0550111222',
        'items' => [[
            'product_variant_id' => $variant->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]],
    ];

    $results = app(\App\Domains\Order\Services\OrderDuplicateService::class)->findSimilar($candidate);

    expect($results)->toBeEmpty();
});

test('findSimilar ignores candidates from another store', function () {
    [$user, $store] = dupUser(StoreRoleEnum::OWNER->value);
    $otherStore = Store::create([
        'user_id' => $user->id,
        'name' => 'Other Store',
        'slug' => 'other-store-'.uniqid(),
        'status' => 'active',
    ]);
    [$product, $variant] = dupVariant($store);
    dupOrder($otherStore, '0550333444', [$variant->id], 'confirmed');

    $candidate = [
        'store_id' => $store->id,
        'exclude_id' => null,
        'customer_phone' => '0550333444',
        'items' => [[
            'product_variant_id' => $variant->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]],
    ];

    $results = app(\App\Domains\Order\Services\OrderDuplicateService::class)->findSimilar($candidate);

    expect($results)->toBeEmpty();
});

test('form modal shows duplicate warnings after the customer phone matches an earlier order', function () {
    [$user, $store] = dupUser(StoreRoleEnum::OWNER->value);
    [$product, $variant] = dupVariant($store);

    dupOrder($store, '0550666777', [$variant->id], 'in_transit');

    $component = dupVolt([$user, $store])
        ->call('openCreateModal')
        ->set('form.customer_phone', '0550666777')
        ->call('addFormItem', $variant->id);

    expect($component->get('formDuplicateWarnings'))->toHaveCount(1);

    $component->assertSee(__('order_flow.duplicate_detected', ['count' => 1]));
});

test('editing an order excludes itself from the duplicate warnings', function () {
    [$user, $store] = dupUser(StoreRoleEnum::OWNER->value);
    [$product, $variant] = dupVariant($store);

    $order = dupOrder($store, '0550555666', [$variant->id], 'confirmed');

    dupVolt([$user, $store])
        ->call('openEditModal', $order->id)
        ->assertSet('formDuplicateWarnings', []);
});