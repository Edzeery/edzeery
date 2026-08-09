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

        if ($id = session('current_store_id')) {
            return Store::find($id);
        }

        // Stateless API: the client identifies the tenant via an
        // `X-Store-Id` header (or `store_id` query param). Membership
        // is enforced so a token can never leak cross-tenant data.
        if ($id = request()->header('X-Store-Id') ?: request()->query('store_id')) {
            $store = Store::find($id);

            if ($store && auth()->user()?->stores()->where('stores.id', $store->id)->exists()) {
                return $store;
            }
        }

        return null;
    }
}
