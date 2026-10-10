<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Plans\Plan;

class LandingPageController extends Controller
{
    public function index()
    {
        $plans = Plan::query()
            ->where('is_active', true)
            ->public()
            ->with([
                'prices',
                'features' => fn ($q) => $q->orderBy('id'),
            ])
            ->orderByDesc('is_default')
            ->get();
        $storeCount = AllStoresCount();
        $orderDeliveredCount = AllOrdersCountByStatus(\App\Enums\Store\OrderStatus::DELIVERED);
        $totalTransactions =  AllOrdersCount();
        $userCount = AllUsersCount();
        $totalRevenue    = ordersRevenue();
        return view('landing.index', compact(
            'plans',
            'storeCount',
            'userCount',
            'totalTransactions',
            'totalRevenue',
            'orderDeliveredCount',
        ));
    }

    public function contact()
    {
        return view('landing.contact');
    }
}
