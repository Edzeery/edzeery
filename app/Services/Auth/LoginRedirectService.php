<?php

namespace App\Services\Auth;

use App\Models\User;

class LoginRedirectService
{
    public function handle(User $user): string
    {
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return route('filament.admin.pages.dashboard');
        }

        if ($user->isMerchant() || isStoreMember($user)) {
            if ($redirect = $this->autoSelectStore($user)) {
                return $redirect;
            }
        }

        return route('merchant.choose-store');
    }

    protected function autoSelectStore(User $user): ?string
    {
        $activeMemberships = $user->storeMemberships()
            ->with('store')
            ->whereHas(
                'store',
                fn ($q) => $q->where('status', 'active')
            )
            ->get();

        if ($activeMemberships->count() === 1) {
            $membership = $activeMemberships->first();
            session(['current_store_id' => $membership->store_id]);

            return route('merchant.dashboard', ['store' => $membership->store->slug]);
        }

        if ($activeMemberships->count() > 1) {
            return route('merchant.choose-store');
        }

        return route('merchant.choose-store');
    }
}
