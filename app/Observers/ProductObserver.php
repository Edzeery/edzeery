<?php
namespace App\Observers;

use App\Models\Products\Product;

class ProductObserver
{
    public function creating(Product $Product): void
    {
        // Multi-tenant: the store comes from the resolved tenant context
        // (Filament tenant, session or API `X-Store-Id` header) — never from
        // the authenticated user row. Only fall back when no store is set yet.
        if ($Product->store_id === null && $store = currentStore()) {
            $Product->store()->associate($store);
        }
    }

}
