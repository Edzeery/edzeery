<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'store_id'            => $this->store_id,
            'brand_id'            => $this->brand_id,
            'primary_category_id' => $this->primary_category_id,
            'name'                => $this->name,
            'slug'                => $this->slug,
            'sku'                 => $this->sku,
            'barcode'             => $this->barcode,
            'type'                => $this->type,
            'short_description'   => $this->short_description,
            'description'         => $this->description,
            'price'               => $this->price !== null ? (string) $this->price : null,
            'cost_price'          => $this->cost_price !== null ? (string) $this->cost_price : null,
            'unit'                => $this->unit,
            'meta_title'          => $this->meta_title,
            'meta_description'    => $this->meta_description,
            'is_active'           => (bool) $this->is_active,
            'is_featured'         => (bool) $this->is_featured,
            'sort_order'          => (int) $this->sort_order,
            'primary_image'       => $this->whenLoaded('primaryImage', fn () => $this->primaryImage?->path),
            'created_at'          => $this->created_at?->toISOString(),
            'updated_at'          => $this->updated_at?->toISOString(),
        ];
    }
}
