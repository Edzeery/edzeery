<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;

class SetLocale
{
    public function handle($request, Closure $next)
    {
        $host = $request->getHost();
        $domain = config('app.domain', 'edzeery.com');
        $isStorefront = $host !== $domain && str_ends_with($host, '.' . $domain);

        $sessionKey = $isStorefront ? 'storeLocale' : 'locale';
        $cookieKey  = $isStorefront ? 'storeLocale' : 'lang';

        $locale = null;

        if ($request->has('lang')) {
            $locale = $request->query('lang');
        }

        if (!$locale && $request->session()->has($sessionKey)) {
            $locale = $request->session()->get($sessionKey);
        }

        if (!$locale && Cookie::has($cookieKey)) {
            $locale = Cookie::get($cookieKey);
        }

        if (!$locale) {
            $locale = $request->getPreferredLanguage(['ar', 'fr', 'en', 'es']);
        }

        $locale = $locale ?: config('app.locale', 'en');

        if (!in_array($locale, ['ar', 'fr', 'en', 'es'], true)) {
            $locale = config('app.locale', 'en');
        }

        $request->session()->put($sessionKey, $locale);
        Cookie::queue($cookieKey, $locale, 60 * 24 * 365);

        App::setLocale($locale);

        return $next($request);
    }
}
