<?php

use App\Enums\Store\StoreRoleEnum;
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
});

function pickUser(): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate(StoreRoleEnum::OWNER->value, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Picker Store',
        'slug' => 'picker-'.uniqid(),
        'status' => 'active',
    ]);

    StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'invited_by' => $user->id,
        'is_active' => true,
        'role' => StoreRoleEnum::OWNER->value,
    ]);

    return [$user, $store];
}

function pickProduct(Store $store, string $label, bool $active = true): void
{
    $product = Product::create([
        'store_id' => $store->id,
        'name' => "{$label} Product",
        'slug' => 'picker-'.strtolower($label).'-'.uniqid(),
        'sku' => 'picker-sku-'.uniqid(),
        'type' => 'variable',
        'price' => 500,
        'is_active' => $active,
    ]);

    ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Default',
        'sku' => 'picker-v-'.uniqid(),
        'price' => 500,
        'stock' => 100,
        'is_active' => true,
    ]);
}

function pickVolt(array $userStore): object
{
    [$user, $store] = $userStore;
    actingAs($user)->withSession(['current_store_id' => $store->id]);

    return Volt::test('merchant.orders.index');
}

test('the picker pool loads 100 products, then appends the next chunk with a hasMore guard', function () {
    [$user, $store] = pickUser();
    foreach (range(1, 130) as $i) {
        pickProduct($store, "Elite $i");
    }

    $volt = pickVolt([$user, $store]);
    $volt->call('openCreateModal');

    expect(count($volt->get('formProductResults')))->toBe(100)
        ->and($volt->get('productHasMore'))->toBeTrue();

    $volt->call('loadProductChunk');

    expect(count($volt->get('formProductResults')))->toBe(130)
        ->and($volt->get('productHasMore'))->toBeFalse();

    // Guard: once exhausted, further chunk requests are a no-op.
    $volt->call('loadProductChunk');

    expect(count($volt->get('formProductResults')))->toBe(130);
});

test('the picker pool is scoped to the store and excludes inactive products', function () {
    [$user, $store] = pickUser();
    [$otherUser, $otherStore] = pickUser();

    pickProduct($store, 'Mine Active');
    pickProduct($store, 'Mine Hidden', false);
    pickProduct($otherStore, 'Other Store');

    $volt = pickVolt([$user, $store])->call('openCreateModal');
    $names = collect($volt->get('formProductResults'))->pluck('product_name')->all();

    expect($names)->toContain('Mine Active Product')
        ->not->toContain('Mine Hidden Product')
        ->not->toContain('Other Store Product');
});