<?php

use App\Enums\Store\StoreStatusEnum;
use App\Models\Products\Product;
use App\Models\Stores\Store;
use App\Models\User;
use App\Support\StoreContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(Tests\TestCase::class, RefreshDatabase::class);

/**
 * This file lives in tests/Unit, which tests/Pest.php does not bootstrap, so
 * it is bound to the app explicitly through uses().
 */
function edzeeryProduct(string $storeId, bool $active = true): Product
{
    return Product::query()->create([
        'store_id' => $storeId,
        'name' => 'Product '.Str::random(4),
        'slug' => 'product-'.Str::random(8),
        'sku' => 'sku-'.Str::random(8),
        'is_active' => $active,
    ]);
}

beforeEach(function () {
    $this->user = User::factory()->create();

    $this->storeA = Store::query()->create([
        'user_id' => $this->user->id,
        'name' => 'Store A',
        'slug' => 'store-helper-a-'.Str::random(6),
        'status' => StoreStatusEnum::ACTIVE,
    ]);
    $this->storeB = Store::query()->create([
        'user_id' => $this->user->id,
        'name' => 'Store B',
        'slug' => 'store-helper-b-'.Str::random(6),
        'status' => StoreStatusEnum::PENDING,
    ]);
    $this->storeC = Store::query()->create([
        'user_id' => $this->user->id,
        'name' => 'Store C',
        'slug' => 'store-helper-c-'.Str::random(6),
        'status' => StoreStatusEnum::CLOSED,
    ]);

    app(StoreContext::class)->clear();
});

it('groups store counts per status', function () {
    $counts = storeStatusCounts();

    expect($counts[StoreStatusEnum::ACTIVE->value])->toBe(1);
    expect($counts[StoreStatusEnum::PENDING->value])->toBe(1);
    expect($counts[StoreStatusEnum::CLOSED->value])->toBe(1);
    expect(array_sum($counts))->toBe(3);

    $manual = Store::query()
        ->selectRaw('status, COUNT(*) as aggregate')
        ->groupBy('status')
        ->pluck('aggregate', 'status')
        ->map(fn ($count) => (int) $count)
        ->all();

    expect(storeStatusCounts())->toBe($manual);
});

it('keeps store count helpers in sync with their collections', function () {
    expect(AllStoresCount())->toBe(AllStores()->count());
    expect(AllActiveStoresCount())->toBe(AllActiveStores()->count());
    expect(AllPendingStoresCount())->toBe(AllPendingStores()->count());
    expect(AllClosedStoresCount())->toBe(AllClosedStores()->count());
    expect(AllStoresCountByStatus(StoreStatusEnum::ACTIVE))
        ->toBe(AllStoresByStatus(StoreStatusEnum::ACTIVE)->count());
});

it('aggregates store counts in a single query', function () {
    DB::flushQueryLog();
    DB::enableQueryLog();

    expect(storeStatusCounts())->toBeArray();
    expect(DB::getQueryLog())->toHaveCount(1);
    expect(DB::getQueryLog()[0]['query'])->toContain('group by');

    DB::flushQueryLog();
    expect(AllStoresCount())->toBe(3);
    expect(DB::getQueryLog())->toHaveCount(1);
    expect(DB::getQueryLog()[0]['query'])->toContain('count(');

    DB::flushQueryLog();
    expect(AllActiveStoresCount())->toBe(1);
    expect(DB::getQueryLog())->toHaveCount(1);
});

it('product helpers ignore the StoreScope for a non-admin with a current store', function () {
    edzeeryProduct($this->storeA->id, true);
    edzeeryProduct($this->storeA->id, false);
    edzeeryProduct($this->storeB->id, true);

    $this->actingAs($this->user);
    app(StoreContext::class)->set($this->storeA);

    // The StoreScope itself still narrows a plain Product query to store A.
    expect(Product::query()->count())->toBe(2);

    // Platform-wide product helpers bypass the scope entirely.
    expect(AllProducts())->toHaveCount(3);
    expect(AllProductsCount())->toBe(3);
    expect(AllProductsByActive())->toHaveCount(3);
    expect(AllProductsCountByActive())->toBe(3);
    expect(AllProductsByActive(true))->toHaveCount(2);
    expect(AllProductsCountByActive(true))->toBe(2);
    expect(AllProductsCountByActive(false))->toBe(1);
    expect(AllActiveProductsCount())->toBe(2);
    expect(AllProductsCountByStore($this->storeA->id))->toBe(2);
});

it('aggregates product counts in a single query', function () {
    edzeeryProduct($this->storeA->id, true);
    edzeeryProduct($this->storeB->id, true);
    edzeeryProduct($this->storeB->id, false);

    DB::flushQueryLog();
    DB::enableQueryLog();

    expect(AllProductsCount())->toBe(3);
    expect(DB::getQueryLog())->toHaveCount(1);

    DB::flushQueryLog();
    expect(AllProductsCountByActive(true))->toBe(2);
    expect(DB::getQueryLog())->toHaveCount(1);

    DB::flushQueryLog();
    expect(AllActiveProductsCount())->toBe(2);
    expect(DB::getQueryLog())->toHaveCount(1);

    DB::flushQueryLog();
    expect(AllProductsCountByStore($this->storeB->id))->toBe(2);
    expect(DB::getQueryLog())->toHaveCount(1);
});
