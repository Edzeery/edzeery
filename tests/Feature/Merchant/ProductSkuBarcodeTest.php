<?php

use App\Enums\Store\StoreRoleEnum;
use App\Models\billing\Subscription;
use App\Models\Plans\Plan;
use App\Models\Plans\PlanPrice;
use App\Models\Products\Product;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Services\ProductService;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

use Database\Seeders\PlansSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\StoreRolesAndPermissionsSeeder;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(StoreRolesAndPermissionsSeeder::class);
    $this->seed(PlansSeeder::class);
});

function skuUser(): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate(StoreRoleEnum::OWNER->value, 'merchant'));

    // Use the unlimited plan so products_limit is never exhausted
    $plan = Plan::where('slug', 'enterprise')->first();
    $price = PlanPrice::where('plan_id', $plan->id)->where('billing_period', 'monthly')->first();

    // Wipe any auto-created trial subscription from User::booted
    Subscription::where('user_id', $user->id)->delete();

    Subscription::create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'plan_price_id' => $price?->id,
        'status' => 'active',
        'starts_at' => now(),
    ]);

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'SKU Test Store',
        'slug' => 'sku-test-' . uniqid(),
        'status' => 'active',
    ]);

    StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'invited_by' => $user->id,
        'is_active' => true,
        'role' => StoreRoleEnum::OWNER->value,
    ]);

    Auth::login($user);
    session(['current_store_id' => $store->id]);

    return [$user, $store];
}

function dataShape(string $slug, string $sku = '', string $barcode = '', bool $autoSku = true, bool $autoBarcode = true): array
{
    return [
        'name' => "Product {$slug}",
        'slug' => $slug,
        'sku' => $sku,
        'barcode' => $barcode,
        'auto_generate_sku' => $autoSku,
        'auto_generate_barcode' => $autoBarcode,
        'has_variants' => false,
        'is_active' => true,
        'is_featured' => false,
        'price' => 100,
        'cost_price' => 50,
        'compare_price' => null,
        'stock' => 10,
        'low_stock_threshold' => 5,
        'description' => null,
        'options' => [],
        'variants_preview' => [],
        'images' => [],
    ];
}

test('creating three products with empty sku and barcode succeeds with auto-generate enabled', function () {
    [$user, $store] = skuUser();
    $service = app(ProductService::class);

    for ($i = 1; $i <= 3; $i++) {
        $product = $service->create($store, dataShape("auto-sku-{$i}"));

        expect($product->sku)->not->toBeEmpty()
            ->and($product->barcode)->not->toBeEmpty();

        // The single default variant mirrors the base product SKU
        $variant = $product->variants()->where('is_default', true)->first();
        expect($variant->sku)->not->toBeEmpty();
    }

    // All three have unique SKUs
    $skus = Product::query()
        ->where('store_id', $store->id)
        ->pluck('sku')
        ->all();

    expect($skus)->toHaveCount(3)
        ->and(array_unique($skus))->toHaveCount(3);
});

test('creating products with explicit unique skus works', function () {
    [$user, $store] = skuUser();
    $service = app(ProductService::class);

    $p1 = $service->create($store, dataShape('explicit-1', 'SKU-001', 'BAR-001', false, false));
    $p2 = $service->create($store, dataShape('explicit-2', 'SKU-002', 'BAR-002', false, false));

    expect($p1->sku)->toBe('SKU-001')
        ->and($p1->barcode)->toBe('BAR-001')
        ->and($p2->sku)->toBe('SKU-002')
        ->and($p2->barcode)->toBe('BAR-002');
});

test('duplicate real sku through the wizard form yields a clean validation error, not a sql exception', function () {
    [$user, $store] = skuUser();
    $service = app(ProductService::class);

    $service->create($store, dataShape('dup-1', 'DUP-SKU', 'DUP-BAR', false, false));

    $volt = Livewire\Volt\Volt::test('merchant.products.form');

    $volt->set([
        'name' => 'Duplicate Product',
        'slug' => 'duplicate-sku-form',
        'sku' => 'DUP-SKU',
        'barcode' => 'DUP-BAR',
        'auto_generate_sku' => false,
        'auto_generate_barcode' => false,
        'brand_id' => null,
        'categories' => [],
        'price' => 100,
        'cost_price' => 50,
        'stock' => 5,
        'low_stock_threshold' => 5,
    ]);

    $volt->call('save');

    $volt->assertHasErrors(['sku'])
        ->assertHasNoErrors(['name', 'slug']);

    // No second product was persisted
    expect(Product::where('store_id', $store->id)->count())->toBe(1);
});

test('empty sku with auto-generate off throws sku_required validation', function () {
    [$user, $store] = skuUser();
    $service = app(ProductService::class);

    $this->expectException(\Illuminate\Validation\ValidationException::class);

    $service->create($store, dataShape('manual-empty', '', '', false, false));
});

test('empty barcode with auto-generate off is stored as null', function () {
    [$user, $store] = skuUser();
    $service = app(ProductService::class);

    $product = $service->create($store, dataShape('null-bar', 'MANUAL-SKU', '', false, false));

    expect($product->sku)->toBe('MANUAL-SKU')
        ->and($product->barcode)->toBeNull();
});

test('update preserves generated sku and does not let empty string override', function () {
    [$user, $store] = skuUser();
    $service = app(ProductService::class);

    $product = $service->create($store, dataShape('update-test'));

    $originalSku = $product->sku;

    $product = $service->update($product, array_merge(
        dataShape('update-test', $originalSku, '', false, false),
        ['auto_generate_sku' => false, 'auto_generate_barcode' => false],
    ));

    // SKU must be preserved as the explicitly provided non-empty value, not overridden
    expect($product->sku)->toBe($originalSku);
});
