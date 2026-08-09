<?php

namespace App\Observers;

use App\Models\Stores\Store;

class StoreObserver
{
    public function creating(Store $store): void
    {
        if (auth()->check()) {
            $store->user_id = auth()->id();
        }
    }
}
