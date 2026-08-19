<?php

namespace App\Http\Controllers\Auth;

use App\Enums\Store\StoreRoleEnum;
use App\Http\Controllers\Controller;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Support\StoreContext;

class ChooseStoreController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // 1) Stores owned by the user
        $ownedStoreIds = Store::where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->pluck('id');

        // 2) Stores where user has an active membership (team member)
        $memberStoreIds = StoreMembership::where('user_id', $user->id)
            ->where('is_active', true)
            ->pluck('store_id');

        // 3) Merge + deduplicate
        $storeIds = $ownedStoreIds->merge($memberStoreIds)->unique();

        if ($storeIds->isEmpty()) {
            return redirect()->route('merchant.create-store');
        }

        // 4) Build a clean collection: store + role + membership
        $stores = Store::whereIn('id', $storeIds)
            ->with(['owner', 'settings'])
            ->get()
            ->map(function (Store $store) use ($user, $memberStoreIds) {
                $isOwner = $store->user_id === $user->id;
                $isMember = $memberStoreIds->contains($store->id);

                // Determine role: owner takes priority
                $role = $isOwner
                    ? StoreRoleEnum::OWNER
                    : ($isMember
                        ? $this->getMembershipRole($user, $store)
                        : StoreRoleEnum::STAFF);

                // Subscription comes from the store OWNER, not the current user
                $subscription = $store->owner?->latestSubscription();

                return (object) [
                    'store' => $store,
                    'role' => $role,
                    'subscription' => $subscription,
                ];
            });

        return view('auth.choose-store', ['stores' => $stores]);
    }

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
