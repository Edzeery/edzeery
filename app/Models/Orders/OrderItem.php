<?php

namespace App\Models\Orders;

use App\Models\Products\ProductVariant;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasUlids;
    protected $fillable = [
        'store_id',
        'order_id',
        'product_variant_id',
        'quantity',
        'price',
        'subtotal',
    ];

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
}
