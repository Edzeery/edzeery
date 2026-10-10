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

        // T-03: API requests resolve ONLY through the explicit X-Store-Id
        // header / store_id query param. There is never a subdomain fallback
        // for authenticated API traffic, and a foreign or unknown store is a
        // hard 403 (no silent downgrade to another tenant).
        if (request()->is('api/*')) {
            return self::resolveFromApi();
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

        $store = self::resolveFromSubdomain();
        if ($store) {
            app(StoreContext::class)->set($store);
        }

        return $store;
    }

    private static function resolveFromApi(): ?Store
    {
        $id = request()->header('X-Store-Id') ?: request()->query('store_id');

        if (! $id) {
            return null;
        }

        $store = Store::find($id);

        $isMember = $store && auth()->user()?->storeMemberships()
            ->where('store_id', $store->id)
            ->where('is_active', true)
            ->exists();

        if (! $isMember) {
            abort(403, 'Forbidden: you are not an active member of the requested store.');
        }

        app(StoreContext::class)->set($store);

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
