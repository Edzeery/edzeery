<?php

namespace App\Domains\Merchant\Actions;

use App\Domains\Merchant\DTOs\StoreCardData;
use App\Enums\Platform\UserRoleEnum;
use App\Enums\Store\StoreRoleEnum;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Models\User;

class GetStoreCardsAction
{
    public function execute(User $user): array
    {
        // 1) Stores owned by the user
        $ownedStoreIds = Store::where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->pluck('id');

        // 2) Stores where user has an active membership
        $memberStoreIds = StoreMembership::where('user_id', $user->id)
            ->where('is_active', true)
            ->pluck('store_id');

        // 3) Merge + deduplicate
        $storeIds = $ownedStoreIds->merge($memberStoreIds)->unique();

        if ($storeIds->isEmpty()) {
            return [];
        }

        // 4) Build store cards
        return Store::whereIn('id', $storeIds)
            ->with(['owner.subscriptions.plan', 'memberships'])
            ->get()
            ->map(function (Store $store) use ($user, $memberStoreIds) {
                $isOwner = $store->user_id === $user->id;
                $isMember = $memberStoreIds->contains($store->id);

                // Role: owner takes priority
                $role = $isOwner
                    ? StoreRoleEnum::OWNER
                    : ($isMember
                        ? $this->resolveMembershipRole($user)
                        : StoreRoleEnum::STAFF);

                // Subscription from the store OWNER
                $subscription = $store->owner?->subscriptions()?->first();
                $status = $store->currentStatus();

                return new StoreCardData(
                    storeId: $store->id,
                    storeSlug: $store->slug,
                    membershipRole: $role,
                    storeName: $store->name,
                    storeLogo: $store->logo,
                    planName: $subscription?->plan?->name ?? 'No Plan',
                    storeStatus: $status,
                    membersCount: $store->memberships()
                        ->where('is_active', true)
                        ->where('user_id', '!=', $user->id)
                        ->distinct()
                        ->count('user_id'),
                    canEnter: true,
                );
            })
            ->map->toArray()
            ->toArray();
    }

    private function resolveMembershipRole(User $user): StoreRoleEnum
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
