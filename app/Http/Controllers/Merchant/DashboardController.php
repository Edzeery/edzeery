<?php

namespace App\Http\Controllers\Merchant;

use App\Domains\Merchant\Actions\GetDashboardStatsAction;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index(GetDashboardStatsAction $getDashboardStatsAction)
    {
        $user = user();

        $stores = $user->stores()->get();

        $stats = $getDashboardStatsAction->execute($user);

        return view('merchant.dashboard', compact('stores', 'stats'));
    }
}
