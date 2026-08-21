<?php

namespace App\Models\Orders;

use App\Models\Products\ProductVariant;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderItem extends Model
{
    use HasUlids;
    use SoftDeletes;
    protected $fillable = [
        'store_id',
        'order_id',
        'product_variant_id',
        'product_id',
        'quantity',
        'price',
        'subtotal',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(\App\Models\Orders\Order::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function product()
    {
        return $this->belongsTo(\App\Models\Products\Product::class);
    }

    public function store()
    {
        return $this->belongsTo(\App\Models\Stores\Store::class);
    }
}
