<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Stores\Team\StoreMembership;
use App\Support\StoreContext;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Request;

class ChooseStoreController extends Controller
{
    public function index()
    {
        $memberships = auth()->user()
            ->storeMemberships()
            ->where('is_active', true)
            ->with('store')
            ->get();

        abort_if($memberships->isEmpty(), 403);

        return view('auth.choose-store', compact('memberships'));
    }

    public function select(StoreMembership $membership)
    {
        abort_unless(
            $membership->user_id === auth()->id(),
            403
        );

        session(['current_store_id' => $membership->store_id]);

        app(StoreContext::class)->set($membership->store);

        Filament::setTenant($membership->store);

        return redirect(
            Filament::getPanel('merchant')->getUrl($membership->store)
        );
    }
}
