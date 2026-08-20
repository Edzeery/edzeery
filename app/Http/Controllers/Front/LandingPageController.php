<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Orders\Order;
use App\Models\Plans\Plan;
use App\Models\Stores\Store;
use App\Models\User;

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
        $storeCount = Store::count();
        $orderCount = Order::count();
        $userCount = User::count();
        return view('landing.index', compact(
            'plans',
            'storeCount',
            'orderCount',
            'userCount',
        ));
    }

    public function contact()
    {
        return view('landing.contact');
    }
}
