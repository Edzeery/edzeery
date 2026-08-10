<?php

namespace App\Services\Auth;

use App\Enums\Store\StoreStatusEnum;
use App\Models\User;
use Filament\Facades\Filament;

use Filament\Panel;

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

    protected function loginToPanel(
        string $panelId,
        User $user,
        ?string $redirect = null
    ): string {
        $panel = Filament::getPanel($panelId);

        // 🔑 تسجيل الدخول على هذا الـ Panel
        $panel->auth()->login($user);

        // 🔑 تعيينه كـ Panel حالي
        Filament::setCurrentPanel($panel);

        return $redirect ?? $panel?->getUrl();
    }

    protected function autoSelectStore(User $user): ?string
    {


        $activeMemberships = $user->storeMemberships()
            ->with('store')
            ->whereHas(
                'store',
                fn($q) =>
                $q->where('is_active', true)
            )
            ->get();
        // dd($activeMemberships->first()->store->currentStatus());

        // 🟢 متجر واحد → دخول مباشر
        if ($activeMemberships->count() === 1) {
            $membership = $activeMemberships->first();

            session(['current_store_id' => $membership->store_id]);
            Filament::setTenant($membership->store);
            if ($user->isMerchant()) {

                return $this->loginToPanel(
                    'merchant',
                    $user,
                    route('account.merchant.dashboard')
                );
            }
            return $this->loginToPanel('merchant', $user);
        }

        // 🟡 أكثر من متجر
        if ($activeMemberships->count() > 1) {
            return $this->loginToPanel('merchant', $user, route('choose-store'));
        }

        // 🔴 لا يوجد أي متجر
        return $this->loginToPanel('merchant', $user, route('filament.merchant.tenant.registration'));
    }
}
