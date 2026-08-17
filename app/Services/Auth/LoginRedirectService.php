<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class LoginRedirectService
{
    public function handle(User $user): string
    {
        if ($user->isMerchant() || isStoreMember() || $user->isUser()) {
            if ($redirect = $this->autoSelectStore($user)) {
                return $redirect;
            }
        }

        return route('login');
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

        // Single store → direct login
        if ($activeMemberships->count() === 1) {
            $membership = $activeMemberships->first();
            session(['current_store_id' => $membership->store_id]);

            return route('merchant.dashboard', $membership->store);
        }

        // Multiple stores → choose
        if ($activeMemberships->count() > 1) {
            return route('choose-store');
        }

        // No stores → create one
        return route('merchant.create-store');
    }
}
