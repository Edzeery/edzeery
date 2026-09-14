<?php

use App\Domains\Orders\Support\OrderItemsFormatter;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Models\Products\Product;
use App\Models\Products\ProductOption;
use App\Models\Products\ProductOptionValue;
use App\Models\Products\ProductVariant;
use App\Models\Stores\Store;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\StoreRolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\SystemStatusesSeeder::class);
});

function fmtUser(): array
{
    $user = roleUser('merchant');

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Formatter Store',
        'slug' => 'fmt-'.uniqid(),
        'status' => 'active',
    ]);

    return [$user, $store];
}

function fmtProduct(Store $store, string $name, float $price = 100): Product
{
    return Product::create([
        'store_id' => $store->id,
        'name' => $name,
        'slug' => strtolower($name).'-'.uniqid(),
        'sku' => strtoupper($name).'-SKU',
        'type' => 'simple',
        'price' => $price,
        'is_active' => true,
    ]);
}

function fmtVariant(Store $store, Product $product, string $sku = '', float $price = 100): ProductVariant
{
    return ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => $product->name.' Default',
        'sku' => $sku !== '' ? $sku : (strtoupper($product->name).'-V-SKU'),
        'price' => $price,
        'stock' => 50,
        'is_active' => true,
    ]);
}

function fmtOption(Store $store, string $optionName): ProductOption
{
    return ProductOption::create([
        'store_id' => $store->id,
        'name' => $optionName,
        'type' => 'radio',
        'sort_order' => 0,
    ]);
}

function fmtOptionValue(Store $store, ProductOption $option, string $valueName): ProductOptionValue
{
    return ProductOptionValue::create([
        'store_id' => $store->id,
        'product_option_id' => $option->id,
        'value' => $valueName,
        'sort_order' => 0,
    ]);
}

function fmtOrder(Store $store, array $itemSpecs): Order
{
    $customer = \App\Models\Customer::create([
        'store_id' => $store->id,
        'name' => 'Fmt Customer',
        'phone' => '0550'.str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT),
        'status' => true,
    ]);

    $status = \App\Models\Status::system()->forType('order')->where('key', 'pending')->first();

    $order = Order::create([
        'store_id' => $store->id,
        'customer_id' => $customer->id,
        'status_id' => $status?->id,
        'number' => (new Order(['store_id' => $store->id]))->nextOrderNumber(),
        'total_amount' => 0,
        'delivery_type' => 'home',
        'payment_method' => 'cod',
        'shipping_cost' => 0,
    ]);

    foreach ($itemSpecs as $spec) {
        OrderItem::create([
            'store_id' => $store->id,
            'order_id' => $order->id,
            'product_variant_id' => $spec['variant']?->id,
            'product_id' => $spec['product']->id,
            'quantity' => $spec['qty'],
            'price' => $spec['price'],
            'subtotal' => $spec['qty'] * $spec['price'],
        ]);
    }

    return $order->load(['items.product', 'items.variant', 'items.variant.optionValues.option']);
}

function fmtFlat($items)
{
    return app(OrderItemsFormatter::class)->toFlatItems($items);
}

function fmtGroups($items)
{
    return app(OrderItemsFormatter::class)->toTableGroups($items);
}

function fmtCompact($items): string
{
    return app(OrderItemsFormatter::class)->toCompactString($items);
}

function fmtDetailed($items): array
{
    return app(OrderItemsFormatter::class)->toDetailedLines($items);
}

// ---------- Formatter unit contracts ----------

test('toFlatItems returns per-line entries with required keys', function () {
    [, $store] = fmtUser();
    $product = fmtProduct($store, 'Shirt', 500);
    $variant = fmtVariant($store, $product, 'SH-001', 500);

    $order = fmtOrder($store, [
        ['product' => $product, 'variant' => $variant, 'qty' => 2, 'price' => 500],
    ]);

    $flat = fmtFlat($order->items);

    expect($flat)->toHaveCount(1);
    expect($flat[0])->toHaveKeys([
        'variant_id', 'product_id', 'name', 'sku', 'price', 'qty',
    ]);
    expect($flat[0]['variant_id'])->toBe((string) $variant->id);
    expect($flat[0]['product_id'])->toBe((string) $product->id);
    expect($flat[0]['name'])->toBe('Shirt');
    expect($flat[0]['sku'])->toBe('SH-001');
    expect($flat[0]['price'])->toBe(500.0);
    expect($flat[0]['qty'])->toBe(2);
});

test('toTableGroups groups variants under their parent product', function () {
    [, $store] = fmtUser();
    $product = fmtProduct($store, 'Hoodie', 800);
    $variant = fmtVariant($store, $product, 'HD-001', 800);
    $option = fmtOption($store, 'Color');
    $optionValue = fmtOptionValue($store, $option, 'Red');

    $variant->optionValues()->attach($optionValue->id, ['product_option_id' => $option->id]);

    $order = fmtOrder($store, [
        ['product' => $product, 'variant' => $variant, 'qty' => 3, 'price' => 800],
    ]);

    $groups = fmtGroups($order->items);

    expect($groups)->toHaveCount(1);
    expect($groups[0]['product_id'])->toBe((string) $product->id);
    expect($groups[0]['product_name'])->toBe('Hoodie');
    expect($groups[0]['chips'])->toHaveCount(1);
    expect($groups[0]['chips'][0]['label'])->toBe('Red');
    expect($groups[0]['chips'][0]['sku'])->toBe('HD-001');
    expect($groups[0]['chips'][0]['qty'])->toBe(3);
    expect($groups[0]['chips'][0]['price'])->toBe(800.0);
    expect($groups[0]['chips'][0])->toHaveKey('variant_id');
});

test('toTableGroups aggregates same-variant lines across orders', function () {
    [, $store] = fmtUser();
    $product = fmtProduct($store, 'Cap', 200);
    $variant = fmtVariant($store, $product, 'CP-001', 200);

    $orderA = fmtOrder($store, [
        ['product' => $product, 'variant' => $variant, 'qty' => 1, 'price' => 200],
    ]);
    $orderB = fmtOrder($store, [
        ['product' => $product, 'variant' => $variant, 'qty' => 3, 'price' => 200],
    ]);

    $items = $orderA->items->concat($orderB->items);

    $groups = fmtGroups($items);

    expect($groups)->toHaveCount(1);
    expect($groups[0]['chips'])->toHaveCount(1);
    expect($groups[0]['chips'][0]['qty'])->toBe(4);
});

test('toTableGroups with multiple variants under one product creates separate chips', function () {
    [, $store] = fmtUser();
    $product = fmtProduct($store, 'Tee', 300);
    $variantA = fmtVariant($store, $product, 'T-RED', 300);
    $variantB = fmtVariant($store, $product, 'T-BLU', 300);
    $option = fmtOption($store, 'Color');
    $red = fmtOptionValue($store, $option, 'Red');
    $blue = fmtOptionValue($store, $option, 'Blue');

    $variantA->optionValues()->attach($red->id, ['product_option_id' => $option->id]);
    $variantB->optionValues()->attach($blue->id, ['product_option_id' => $option->id]);

    $order = fmtOrder($store, [
        ['product' => $product, 'variant' => $variantA, 'qty' => 1, 'price' => 300],
        ['product' => $product, 'variant' => $variantB, 'qty' => 2, 'price' => 300],
    ]);

    $groups = fmtGroups($order->items);

    expect($groups)->toHaveCount(1);
    expect($groups[0]['chips'])->toHaveCount(2);
    expect(collect($groups[0]['chips'])->pluck('label')->toArray())->toBe(['Red', 'Blue']);
    expect(collect($groups[0]['chips'])->pluck('qty')->toArray())->toBe([1, 2]);
});

test('toTableGroups with two different products creates two groups', function () {
    [, $store] = fmtUser();
    $productA = fmtProduct($store, 'Shoes', 1000);
    $variantA = fmtVariant($store, $productA, 'SH-001', 1000);
    $productB = fmtProduct($store, 'Socks', 100);
    $variantB = fmtVariant($store, $productB, 'SK-001', 100);

    $order = fmtOrder($store, [
        ['product' => $productA, 'variant' => $variantA, 'qty' => 1, 'price' => 1000],
        ['product' => $productB, 'variant' => $variantB, 'qty' => 3, 'price' => 100],
    ]);

    $groups = fmtGroups($order->items);

    expect($groups)->toHaveCount(2);
    expect($groups[0]['product_name'])->toBe('Shoes');
    expect($groups[1]['product_name'])->toBe('Socks');
});

test('toTableGroups variant without option labels produces empty-label chip', function () {
    [, $store] = fmtUser();
    $product = fmtProduct($store, 'Gift Card', 50);
    $variant = fmtVariant($store, $product, 'GC-001');

    $order = fmtOrder($store, [
        ['product' => $product, 'variant' => $variant, 'qty' => 1, 'price' => 50],
    ]);

    $groups = fmtGroups($order->items);

    expect($groups)->toHaveCount(1);
    expect($groups[0]['product_name'])->toBe('Gift Card');
    expect($groups[0]['chips'][0]['variant_id'])->toBe((string) $variant->id);
    expect($groups[0]['chips'][0]['label'])->toBe('');
    expect($groups[0]['chips'][0]['qty'])->toBe(1);
});

test('toCompactString joins product names with quantity and labels', function () {
    [, $store] = fmtUser();
    $product = fmtProduct($store, 'Jacket', 2000);
    $variant = fmtVariant($store, $product, 'JK-001', 2000);
    $option = fmtOption($store, 'Size');
    $size = fmtOptionValue($store, $option, 'Large');

    $variant->optionValues()->attach($size->id, ['product_option_id' => $option->id]);

    $order = fmtOrder($store, [
        ['product' => $product, 'variant' => $variant, 'qty' => 2, 'price' => 2000],
    ]);

    $compact = fmtCompact($order->items);

    expect($compact)->toContain('Jacket');
    expect($compact)->toContain('Large');
    expect($compact)->toContain('×2');
});

test('toCompactString returns item count fallback when all product names are blank', function () {
    [, $store] = fmtUser();
    $product = Product::create([
        'store_id' => $store->id,
        'name' => '',
        'slug' => 'blank-'.uniqid(),
        'sku' => 'BLANK',
        'type' => 'simple',
        'price' => 0,
        'is_active' => true,
    ]);
    $variant = fmtVariant($store, $product, 'B-001');

    $order = fmtOrder($store, [
        ['product' => $product, 'variant' => $variant, 'qty' => 3, 'price' => 0],
    ]);

    $compact = fmtCompact($order->items);

    expect($compact)->toMatch('/^\d+ items$/');
});

test('toDetailedLines produces one block per product with bullet variants', function () {
    [, $store] = fmtUser();
    $product = fmtProduct($store, 'Bag', 700);
    $variant = fmtVariant($store, $product, 'BG-001', 700);
    $option = fmtOption($store, 'Color');
    $color = fmtOptionValue($store, $option, 'Black');

    $variant->optionValues()->attach($color->id, ['product_option_id' => $option->id]);

    $order = fmtOrder($store, [
        ['product' => $product, 'variant' => $variant, 'qty' => 2, 'price' => 700],
    ]);

    $lines = fmtDetailed($order->items);

    expect($lines)->toHaveCount(1);
    expect($lines[0])->toContain('Bag');
    expect($lines[0])->toContain('Black');
    expect($lines[0])->toContain('BG-001');
    expect($lines[0])->toContain('×2');
});

test('toDetailedLines without option labels produces compact single line', function () {
    [, $store] = fmtUser();
    $product = fmtProduct($store, 'Notebook', 50);
    $variant = fmtVariant($store, $product, 'NB-001');

    $order = fmtOrder($store, [
        ['product' => $product, 'variant' => $variant, 'qty' => 5, 'price' => 50],
    ]);

    $lines = fmtDetailed($order->items);

    expect($lines)->toHaveCount(1);
    expect($lines[0])->toBe('Notebook ×5');
});

test('toTableGroups multi-option variant joins values with slash', function () {
    [, $store] = fmtUser();
    $product = fmtProduct($store, 'Dress', 600);
    $variant = fmtVariant($store, $product, 'DR-001', 600);
    $sizeOption = fmtOption($store, 'Size');
    $colorOption = fmtOption($store, 'Color');
    $size = fmtOptionValue($store, $sizeOption, 'Medium');
    $color = fmtOptionValue($store, $colorOption, 'Beige');

    $variant->optionValues()->attach($size->id, ['product_option_id' => $sizeOption->id]);
    $variant->optionValues()->attach($color->id, ['product_option_id' => $colorOption->id]);

    $order = fmtOrder($store, [
        ['product' => $product, 'variant' => $variant, 'qty' => 1, 'price' => 600],
    ]);

    $groups = fmtGroups($order->items);

    expect($groups[0]['chips'][0]['label'])->toBe('Medium / Beige');
});

test('toFlatItems empty collection returns empty array', function () {
    expect(fmtFlat(collect())->toArray())->toBe([]);
});

test('toTableGroups empty collection returns empty array', function () {
    expect(fmtGroups(collect())->toArray())->toBe([]);
});

test('toCompactString empty collection returns empty string', function () {
    expect(fmtCompact(collect()))->toBe('');
});

test('toDetailedLines empty collection returns empty array', function () {
    expect(fmtDetailed(collect()))->toBe([]);
});

test('edge: empty-collection compact fallback uses the raw item count', function () {
    [, $store] = fmtUser();
    $product = Product::create([
        'store_id' => $store->id,
        'name' => 'X',
        'slug' => 'x-'.uniqid(),
        'sku' => 'X-SKU',
        'type' => 'simple',
        'price' => 0,
        'is_active' => true,
    ]);
    $variantA = fmtVariant($store, $product, 'XA-001');
    $variantB = fmtVariant($store, $product, 'XB-001');
    $variantC = fmtVariant($store, $product, 'XC-001');

    $order1 = fmtOrder($store, [
        ['product' => $product, 'variant' => $variantA, 'qty' => 1, 'price' => 0],
    ]);
    $order2 = fmtOrder($store, [
        ['product' => $product, 'variant' => $variantB, 'qty' => 1, 'price' => 0],
    ]);
    $order3 = fmtOrder($store, [
        ['product' => $product, 'variant' => $variantC, 'qty' => 1, 'price' => 0],
    ]);

    $items = $order1->items->concat($order2->items)->concat($order3->items);

    $flat = fmtFlat($items);
    $groups = fmtGroups($items);

    expect($flat)->toHaveCount(3);
    expect($groups[0]['chips'])->toHaveCount(3);
    expect(fmtCompact($items))->toBe('X ×3');
});