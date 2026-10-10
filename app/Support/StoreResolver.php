<?php

namespace App\Support;

use App\Models\Stores\Store;
use Filament\Facades\Filament;

class StoreResolver
{
    public static function resolve(): ?Store
    {
        if ($tenant = Filament::getTenant()) {
            return $tenant;
        }

        if ($store = app(StoreContext::class)->get()) {
            return $store;
        }

        if (auth()->check() && $id = session('current_store_id')) {
            $store = Store::find($id);

            // S-03: never trust a poisoned current_store_id. The session value
            // is only legit if the user has an active membership on that store.
            if ($store && auth()->user()?->storeMemberships()
                ->where('store_id', $store->id)
                ->where('is_active', true)
                ->exists()) {
                app(StoreContext::class)->set($store);

                return $store;
            }

            session()->forget('current_store_id');
        }

        if (request()->is('api/*')) {
            if ($id = request()->header('X-Store-Id') ?: request()->query('store_id')) {
                $store = Store::find($id);

                if ($store && auth()->user()?->stores()->where('stores.id', $store->id)->exists()) {
                    app(StoreContext::class)->set($store);

                    return $store;
                }
            }
        }

        $store = self::resolveFromSubdomain();
        if ($store) {
            app(StoreContext::class)->set($store);
        }

        return $store;
    }

    private static function resolveFromSubdomain(): ?Store
    {
        $host = request()->getHost();
        $domain = config('app.domain', 'edzeery.com');

        if (! str_ends_with($host, '.'.$domain)) {
            return null;
        }

        $subdomain = substr($host, 0, -(strlen($domain) + 1));

        if (empty($subdomain)) {
            return null;
        }

        return Store::where('slug', $subdomain)
            ->where('status', 'active')
            ->first();
    }
}
