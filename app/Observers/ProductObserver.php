<?php
namespace App\Observers;

use App\Models\Products\Product;

class ProductObserver
{
    public function creating(Product $Product): void
    {
        if (auth()->check()) {
            $Product->store_id = auth()->user()->store_id;
            // or with a `store` relationship defined:
            $Product->store()->associate(auth()->user()->store);
        }
    }

}
