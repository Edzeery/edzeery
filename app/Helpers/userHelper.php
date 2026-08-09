<?php

use Illuminate\Support\Facades\Auth;

if (! function_exists('getCurrentPanel')) {

    function getCurrentPanel(): string
    {
        return  user()->isSuperAdmin() || user()->isAdmin()
            ? Filament\Facades\Filament::getPanel('admin')->getUrl()
            : (user()->isMerchant()
                ?  route('account.merchant.dashboard')
                : Filament\Facades\Filament::getPanel('merchant')->getUrl());
    }
}



if (!function_exists('currency')) {
    function currency()
    {
        if (Auth::check()) {
            return user()->wallet->currency ?? "DZD";
        }
        return null;
    }
}


if (!function_exists('user_setting')) {
    function user_setting($key = null, $default = null)
    {
        if (!Auth::check()) {
            return system_setting($key, $default); // إذا لم يكن مسجل دخول، نرجع إعداد النظام
        }

        $user = auth()->user();

        // إنشاء سجل إعدادات افتراضي إن لم يوجد
        if (!$user->settings) {
            return system_setting($key, $default);
        }


        $settings = $user->settings?->preferences ?? [];

        // لا يوجد مفتاح => نرجع الكل
        if (is_null($key)) {
            return $settings;
        }

        // مفتاح واحد
        if (is_string($key)) {
            return $settings[$key] ?? system_setting($key, $default);
        }

        // عدة مفاتيح
        if (is_array($key)) {
            return collect($key)->mapWithKeys(fn($k) => [
                $k => $settings[$k] ?? system_setting($k, $default)
            ])->toArray();
        }

        return $default;
    }
}


if (!function_exists('membershipRoles')) {
    function membershipRole()
    {
        return currentMembership()->membershipRole()->name;
    }
}
