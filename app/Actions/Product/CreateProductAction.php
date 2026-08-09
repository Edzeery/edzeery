<?php

namespace App\Actions\Product;

use App\Models\Products\Product;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use App\Support\SkuGenerator;
use App\Services\BarcodeService;

class CreateProductAction
{
    public function handle($store, array $data): Product
    {
        return DB::transaction(function () use ($store, $data) {

            // تحديد النوع
            $type = !empty($data['has_variants']) ? 'variable' : 'simple';

            // توليد SKU
            $sku = $data['auto_generate_sku'] ?? false
                ? SkuGenerator::product($store->slug, $data['slug'])
                : ($data['sku'] ?? null);

            if (!$sku) {
                throw new \DomainException('SKU is required.');
            }

            // توليد Barcode
            $barcode = $data['auto_generate_barcode'] ?? false
                ? BarcodeService::product(null)
                : ($data['barcode'] ?? null);

            $product = Product::create(array_merge(
                [
                    'store_id' => $store->id,
                    'type' => $type,
                    'sku' => $sku,
                    'barcode' => $barcode,
                    'description' => $data['description'] ?? null,
                ],
                Arr::except($data, ['images','variants_preview','has_variants','auto_generate_sku','auto_generate_barcode'])
            ));

            // Sync Images
            if (!empty($data['images'])) {
                app(SyncImagesAction::class)->handle($product, $data['images']);
            }

            // Variants
            if (!empty($data['has_variants'])) {
                app(SyncVariantsAction::class)->handle($product, $data);
            } else {
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
