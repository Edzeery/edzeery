<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class BillingController extends Controller
{
    public function index()
    {
        $stores = user()->stores()->get();

        return view('merchant.billing.index', compact('stores'));
    }
}
