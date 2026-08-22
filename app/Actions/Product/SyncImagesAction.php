<?php

namespace App\Actions\Product;

use App\Models\Products\Product;

class SyncImagesAction
{
    public function handle(Product $product, array $images): void
    {
        foreach (array_values($images) as $index => $path) {
            $product->images()->create([
                'path' => $path,
                'store_id' => $product->store_id,
                'sort_order' => $index,
            ]);
        }
    }
}
