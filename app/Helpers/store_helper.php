<?php

use App\Enums\Store\StoreStatusEnum;
use App\Models\Products\Product;
use App\Models\Stores\Store;

// ==== Stores ====

if (! function_exists('AllStores')) {
    function AllStores()
    {
        return Store::query()->get();
    }
}

if (! function_exists('AllStoresCount')) {
    function AllStoresCount()
    {
        return Store::query()->count();
    }
}

if (! function_exists('AllActiveStores')) {
    function AllActiveStores()
    {
        return Store::query()->where('status', StoreStatusEnum::ACTIVE)->get();
    }
}

if (! function_exists('AllActiveStoresCount')) {
    function AllActiveStoresCount()
    {
        return Store::query()->where('status', StoreStatusEnum::ACTIVE)->count();
    }
}

if (! function_exists('AllPendingStores')) {
    function AllPendingStores()
    {
        return Store::query()->where('status', StoreStatusEnum::PENDING)->get();
    }
}

if (! function_exists('AllPendingStoresCount')) {
    function AllPendingStoresCount()
    {
        return Store::query()->where('status', StoreStatusEnum::PENDING)->count();
    }
}

if (! function_exists('AllClosedStores')) {
    function AllClosedStores()
    {
        return Store::query()->where('status', StoreStatusEnum::CLOSED)->get();
    }
}

if (! function_exists('AllClosedStoresCount')) {
    function AllClosedStoresCount()
    {
        return Store::query()->where('status', StoreStatusEnum::CLOSED)->count();
    }
}

if (! function_exists('AllStoresByStatus')) {
    function AllStoresByStatus(StoreStatusEnum $status)
    {
        return Store::query()->where('status', $status)->get();
    }
}

if (! function_exists('AllStoresCountByStatus')) {
    function AllStoresCountByStatus(StoreStatusEnum $status)
    {
        return Store::query()->where('status', $status)->count();
    }
}

if (! function_exists('storeStatusCounts')) {
    /**
     * One grouped query returning status value => store count.
     *
     * @return array<string, int>
     */
    function storeStatusCounts(): array
    {
        return Store::query()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->map(static fn ($count): int => (int) $count)
            ->all();
    }
}

// ==== Products ====

// Platform-wide product helpers bypass every global scope, including the
// StoreScope, so their result never depends on the current user or store.

if (! function_exists('AllProducts')) {
    function AllProducts()
    {
        return Product::withoutGlobalScopes()->get();
    }
}

if (! function_exists('AllProductsCount')) {
    function AllProductsCount()
    {
        return Product::withoutGlobalScopes()->count();
    }
}

if (! function_exists('AllProductsByActive')) {
    function AllProductsByActive(?bool $active = null)
    {
        $query = Product::withoutGlobalScopes();

        if ($active !== null) {
            $query->where('is_active', $active);
        }

        return $query->get();
    }
}

if (! function_exists('AllProductsCountByActive')) {
    function AllProductsCountByActive(?bool $active = null)
    {
        $query = Product::withoutGlobalScopes();

        if ($active !== null) {
            $query->where('is_active', $active);
        }

        return $query->count();
    }
}

if (! function_exists('AllActiveProductsCount')) {
    function AllActiveProductsCount()
    {
        return AllProductsCountByActive(true);
    }
}

if (! function_exists('AllProductsCountByStore')) {
    function AllProductsCountByStore(string $storeId)
    {
        return Product::withoutGlobalScopes()->where('store_id', $storeId)->count();
    }
}
