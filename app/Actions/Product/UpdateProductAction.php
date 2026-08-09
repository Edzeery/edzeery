<?php

namespace App\Actions\Product;

use App\Models\Products\Product;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use App\Support\SkuGenerator;
use App\Services\BarcodeService;

class UpdateProductAction
{
    public function handle(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data) {

            // تحديث SKU / Barcode
            $sku = $data['auto_generate_sku'] ?? false
                ? SkuGenerator::product($product->store->slug, $data['slug'])
                : ($data['sku'] ?? $product->sku);

            $barcode = $data['auto_generate_barcode'] ?? false
                ? BarcodeService::product(null)
                : ($data['barcode'] ?? $product->barcode);

            $product->update(array_merge(
                [
                    'sku' => $sku,
                    'barcode' => $barcode,
                    'type' => !empty($data['has_variants']) ? 'variable' : 'simple',
                    'description' => $data['description'] ?? $product->description,
                ],
                Arr::except($data, ['images','variants_preview','has_variants','auto_generate_sku','auto_generate_barcode'])
            ));

            // Images
            if (!empty($data['images'])) {
                $product->images()->delete();
                app(SyncImagesAction::class)->handle($product, $data['images']);
            }

            // Variants
            if (!empty($data['has_variants'])) {
                if (!empty($data['options_changed'])) {
                    $product->variants()->delete();
                    app(SyncVariantsAction::class)->handle($product, $data);
                }
            } else {
                $product->variants()->delete();
                $product->variants()->create([
                    'name' => $product->name,
                    'sku' => $sku,
                    'barcode' => $barcode,
                    'price' => $data['price'] ?? 0,
                    'cost_price' => $data['cost_price'] ?? 0,
                    'is_default' => true,
                ]);
            }

            return $product;
        });
    }
}
