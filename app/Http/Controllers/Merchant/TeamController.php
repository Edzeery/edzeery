<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;

class TeamController extends Controller
{
    public function index()
    {

        // عرض كل الفرق لكل متجر ينتمي إليه التاجر
        $stores = user()->stores()->with(['team.user'])->get();

        return view('merchant.team.index', compact('stores'));
    }

    public function create()
    {
        $stores = user()->stores()->get();

        return view('merchant.team.create', compact('stores'));
    }

    public function edit(StoreMembership $member)
    {
        return view('merchant.team.edit', compact('member'));
    }
}
