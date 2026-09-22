<?php

namespace App\Http\Controllers\Auth;

use App\Enums\Store\StoreRoleEnum;
use App\Http\Controllers\Controller;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Support\StoreContext;

class ChooseStoreController extends Controller
{
    public function select(Store $store)
    {
        $user = auth()->user();

        // Must be owner OR active member
        $isOwner = $store->user_id === $user->id;
        $isMember = StoreMembership::where('store_id', $store->id)
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->exists();

        abort_unless($isOwner || $isMember, 403);

        session(['current_store_id' => $store->id]);

        app(StoreContext::class)->set($store);

        return redirect()->route('merchant.dashboard', ['store' => $store->slug]);
    }

    private function getMembershipRole($user, Store $store): StoreRoleEnum
    {
        $user->guard_name = 'merchant';
        $permissions = $user->getAllPermissions()->pluck('name')->toArray();

        if (in_array('store.delete.final', $permissions)) {
            return StoreRoleEnum::OWNER;
        }
        if (in_array('store.settings.sensitive', $permissions)) {
            return StoreRoleEnum::ADMIN;
        }
        if (in_array('team.manage.own', $permissions)) {
            return StoreRoleEnum::MANAGER;
        }

        return StoreRoleEnum::STAFF;
    }
}
