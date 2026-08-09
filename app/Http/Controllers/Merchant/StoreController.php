<?php

namespace App\Http\Controllers\Merchant;

use App\Domains\Merchant\Actions\GetStoreCardsAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Stores\Store;
use Illuminate\Support\Facades\Auth;

class StoreController extends Controller
{
    public function index(GetStoreCardsAction $getStoreCardsAction)
    {
        $storeCards = $getStoreCardsAction->execute(user());


        return view('merchant.stores.index', compact('storeCards'));
    }

    public function create()
    {
        if (user()->subs) {
            # code...
        }
        return view('merchant.stores.create');
    }

    public function edit(Store $store)
    {
        $this->authorize('update', $store); // تحقق صلاحية التاجر

        return view('merchant.stores.edit', compact('store'));
    }
}
