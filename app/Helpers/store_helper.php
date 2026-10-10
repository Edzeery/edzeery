<?php

use App\Enums\Store\OrderStatus;
use App\Enums\Store\StoreStatusEnum;
use App\Models\Stores\Store;

if (! function_exists('AllStores')) {
    function AllStores()
    {
        return Store::all();
    }
}

if (! function_exists('AllStoresCount')) {
    function AllStoresCount()
    {
        return AllStores()->count();
    }
}

if (! function_exists('AllActiveStores')) {
    function AllActiveStores()
    {
        return Store::where('status', StoreStatusEnum::ACTIVE)->get();
    }
}

if (! function_exists('AllActiveStoresCount')) {
    function AllActiveStoresCount()
    {
        return AllActiveStores()->count();
    }
}

if (! function_exists('AllPendingStores')) {
    function AllPendingStores()
    {
        return Store::where('status', StoreStatusEnum::PENDING)->get();
    }
}

if (! function_exists('AllPendingStoresCount')) {
    function AllPendingStoresCount()
    {
        return AllPendingStores()->count();
    }
}

if (! function_exists('AllClosedStores')) {
    function AllClosedStores()
    {
        return Store::where('status', StoreStatusEnum::CLOSED)->get();
    }
}

if (! function_exists('AllClosedStoresCount')) {
    function AllClosedStoresCount()
    {
        return AllClosedStores()->count();
    }
}

if (! function_exists('AllStoresByStatus')) {
    function AllStoresByStatus(StoreStatusEnum $status)
    {
        return Store::where('status', $status)->get();
    }
}

if (! function_exists('AllStoresCountByStatus')) {
    function AllStoresCountByStatus(StoreStatusEnum $status)
    {
        return AllStoresByStatus($status)->count();
    }
}

if (! function_exists('AllStoresByStatusAndType')) {
    function AllStoresByStatusAndType(StoreStatusEnum $status, string $type)
    {
        return Store::where('status', $status)->where('type', $type)->get();
    }
}

if (! function_exists('AllStoresCountByStatusAndType')) {
    function AllStoresCountByStatusAndType(StoreStatusEnum $status, string $type)
    {
        return AllStoresByStatusAndType($status, $type)->count();
    }
}

if (! function_exists('AllStoresByType')) {
    function AllStoresByType(string $type)
    {
        return Store::where('type', $type)->get();
    }
}

if (! function_exists('AllStoresCountByType')) {
    function AllStoresCountByType(string $type)
    {
        return AllStoresByType($type)->count();
    }
}

if (! function_exists('AllStoresByStatusAndTypeAndCountry')) {
    function AllStoresByStatusAndTypeAndCountry(StoreStatusEnum $status, string $type, string $country)
    {
        return Store::where('status', $status)->where('type', $type)->where('country', $country)->get();
    }
}

if (! function_exists('AllStoresCountByStatusAndTypeAndCountry')) {
    function AllStoresCountByStatusAndTypeAndCountry(StoreStatusEnum $status, string $type, string $country)
    {
        return AllStoresByStatusAndTypeAndCountry($status, $type, $country)->count();
    }
}

if (! function_exists('AllStoresByTypeAndCountry')) {
    function AllStoresByTypeAndCountry(string $type, string $country)
    {
        return Store::where('type', $type)->where('country', $country)->get();
    }
}

if (! function_exists('AllStoresCountByTypeAndCountry')) {
    function AllStoresCountByTypeAndCountry(string $type, string $country)
    {
        return AllStoresByTypeAndCountry($type, $country)->count();
    }
}
if (! function_exists('AllStoresByCountry')) {
    function AllStoresByCountry(string $country)
    {
        return Store::where('country', $country)->get();
    }
}

if (! function_exists('AllStoresCountByCountry')) {
    function AllStoresCountByCountry(string $country)
    {
        return AllStoresByCountry($country)->count();
    }
}


// ==== products ====

if (! function_exists('AllProducts')) {
    function AllProducts()
    {
        return \App\Models\Products\Product::all();
    }
}

if (! function_exists('AllProductsCount')) {
    function AllProductsCount()
    {
        return AllProducts()->count();
    }
}



if (! function_exists('AllProductsByStatus')) {
    function AllProductsByStatus($status = null)
    {
        $query = \App\Models\Products\Product::query();
        if ($status !== null) {
            $query->where('status', $status);
        }
        return $query->get();
    }
}

if (! function_exists('AllProductsCountByStatus')) {
    function AllProductsCountByStatus($status = null)
    {
        return AllProductsByStatus($status)->count();
    }
}


// ==== Orders ===

if (! function_exists('AllOrders')) {
    function AllOrders()
    {
        return \App\Models\Orders\Order::all();
    }
}

if (! function_exists('AllOrdersCount')) {
    function AllOrdersCount()
    {
        return AllOrders()->count();
    }
}

if (! function_exists('AllOrdersByStatus')) {
    function AllOrdersByStatus(OrderStatus $status)
    {
        $query = \App\Models\Orders\Order::query();
        if ($status !== null) {
            $query->with('status')->where('key', $status);
        }
        return $query->get();
    }
}

if (! function_exists('AllOrdersCountByStatus')) {
    function AllOrdersCountByStatus(OrderStatus $status)
    {
        return AllOrdersByStatus($status)->count();
    }
}


if (! function_exists('AllOrdersByStore')) {
    function AllOrdersByStore($storeId)
    {
        return \App\Models\Orders\Order::where('store_id', $storeId)->get();
    }
}

if (! function_exists('AllOrdersCountByStore')) {
    function AllOrdersCountByStore($storeId)
    {
        return AllOrdersByStore($storeId)->count();
    }
}

if (! function_exists('AllOrdersByStoreAndStatus')) {
    function AllOrdersByStoreAndStatus($storeId, OrderStatus $status = null)
    {
        $query = \App\Models\Orders\Order::where('store_id', $storeId);
        if ($status !== null) {
            $query->where('status', $status);
        }
        return $query->get();
    }
}

if (! function_exists('AllOrdersCountByStoreAndStatus')) {
    function AllOrdersCountByStoreAndStatus($storeId, OrderStatus $status = null)
    {
        return AllOrdersByStoreAndStatus($storeId, $status)->count();
    }
}

if (! function_exists('AllOrdersByStoreAndStatusAndDateRange')) {
    function AllOrdersByStoreAndStatusAndDateRange($storeId, OrderStatus $status = null, $startDate = null, $endDate = null)
    {
        $query = \App\Models\Orders\Order::where('store_id', $storeId);
        if ($status !== null) {
            $query->where('status', $status);
        }
        if ($startDate !== null) {
            $query->where('created_at', '>=', $startDate);
        }
        if ($endDate !== null) {
            $query->where('created_at', '<=', $endDate);
        }
        return $query->get();
    }
}

if (! function_exists('AllOrdersCountByStoreAndStatusAndDateRange')) {
    function AllOrdersCountByStoreAndStatusAndDateRange($storeId, OrderStatus $status = null, $startDate = null, $endDate = null)
    {
        return AllOrdersByStoreAndStatusAndDateRange($storeId, $status, $startDate, $endDate)->count();
    }
}

if (! function_exists('AllOrdersByStatusAndDateRange')) {
    function AllOrdersByStatusAndDateRange(OrderStatus $status = null, $startDate = null, $endDate = null)
    {
        $query = \App\Models\Orders\Order::query();
        if ($status !== null) {
            $query->where('status', $status);
        }
        if ($startDate !== null) {
            $query->where('created_at', '>=', $startDate);
        }
        if ($endDate !== null) {
            $query->where('created_at', '<=', $endDate);
        }
        return $query->get();
    }
}

if (! function_exists('AllOrdersCountByStatusAndDateRange')) {
    function AllOrdersCountByStatusAndDateRange(OrderStatus $status = null, $startDate = null, $endDate = null)
    {
        return AllOrdersByStatusAndDateRange($status, $startDate, $endDate)->count();
    }
}

if (! function_exists('total_amount')) {
    function total_amount()
    {
        return AllOrders()->sum('total_amount');

    }
}


