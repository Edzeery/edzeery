<?php

namespace App\Http\Middleware\Store;

use App\Support\StoreContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class SetStoreLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $storeContext = app(StoreContext::class);
        $store = $storeContext->get();

        $supported = [];
        $default = config('app.locale', 'en');

        if ($store) {
            $hasCol = Schema::hasColumn('store_settings', 'supported_languages');
            $settings = $store->settings ?? null;
            $supported = $hasCol ? ($settings?->supported_languages ?? []) : [];
            $supported = array_values(array_filter($supported));
            $default = $settings?->language ?? $default;
        }

        $allowed = !empty($supported) ? $supported : ['ar', 'fr', 'en', 'es'];

        $storeLocale = null;

        if ($request->has('lang')) {
            $storeLocale = $request->query('lang');
        }

        if (!$storeLocale && $request->session()->has('storeLocale')) {
            $storeLocale = $request->session()->get('storeLocale');
        }

        if (!$storeLocale && Cookie::has('storeLocale')) {
            $storeLocale = Cookie::get('storeLocale');
        }

        if (!$storeLocale) {
            $storeLocale = $request->getPreferredLanguage($allowed);
        }

        $storeLocale = $storeLocale ?: $default;

        if (!in_array($storeLocale, $allowed, true)) {
            $storeLocale = $default;
        }

        $request->session()->put('storeLocale', $storeLocale);
        Cookie::queue('storeLocale', $storeLocale, 60 * 24 * 365);

        App::setLocale($storeLocale);

        return $next($request);
    }
}
