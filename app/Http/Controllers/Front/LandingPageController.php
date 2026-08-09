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
            ->with([
                'prices',
                'features' => fn ($q) => $q->orderBy('id'),
            ])
            ->orderByDesc('is_default')
            ->get();

        return view('landing.index', compact('plans'));
    }
}
