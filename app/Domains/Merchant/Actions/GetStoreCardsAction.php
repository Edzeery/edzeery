<?php

namespace App\Domains\Merchant\Actions;

use App\Domains\Merchant\DTOs\StoreCardData;
use App\Enums\Platform\UserRoleEnum;
use App\Enums\Store\StoreRoleEnum;
use App\Models\User;

class GetStoreCardsAction
{
    public function execute(User $user): array
    {


        return $user->storeMemberships()
            ->with([
                'store.user.subscriptions.plan',
                'store.memberships',
            ])
            ->get()
            ->map(function ($membership ) {
                $store = $membership->store;
                $membershipRole = in_array(
                    $membership->user?->merchantRole->first()->name,
                    UserRoleEnum::values(),
                )
                    ?  UserRoleEnum::from(
                        $membership->user?->merchantRole->first()->name,
                    )
                    :  StoreRoleEnum::from(
                        $membership->user?->merchantRole->first()->name,
                    );

                $subscription = $membership->user->subscriptions()->first();

                // dd($subscription);
                $status = $store->currentStatus();

                return new StoreCardData(
                    membershipId: $membership->id,
                    membershipRole: $membershipRole,
                    storeName: $store->name,
                    storeLogo: $store->logo,
                    planName: $subscription?->plan?->name ?? 'No Plan',
                    storeStatus: $status,
                    membersCount: $store->memberships()
                        ->where('is_active', true)
                        ->where('user_id', '!=', user()->id)
                        ->distinct()
                        ->count('user_id'),
                    canEnter: $subscription !== null,
                );
            })
            ->map
            ->toArray()
            ->toArray();
    }
}
