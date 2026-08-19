<?php

namespace App\Http\Middleware;

use App\Enums\Store\StoreStatusEnum;
use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class SetLocale
{
    public function handle($request, Closure $next)
    {
        $locale= null;
        // 🔹 1. أولوية للغة القادمة من GET أو POST (تبديل يدوي)
        if ($locale) {
            if ($locale && Auth::check()) {

                // set_user_setting('language', $locale);
            }
        }

        if (!$locale && Auth::check()) {
            // $userLocale = user_setting('language');
            // if ($userLocale) {
            //     $locale = $userLocale;
            // }
        }

        if (!$locale && session()->has('locale')) {
            $locale = session('locale');
        }

        if (!$locale && isset($_COOKIE['lang'])) {
            $locale = $_COOKIE['lang'];
        }
        $locale = $locale ?: config('app.locale');
        session(['locale' => $locale]);
        setcookie('lang', $locale, time() + (365 * 24 * 60 * 60), '/');

        App::setLocale($locale);
        
        return $next($request);
    }
}
