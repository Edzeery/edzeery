<?php

namespace App\Models\Products;

use App\Models\Stores\Store;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductOptionValue extends Model
{
     use HasUlids;
    protected $fillable = [
        'store_id',
        'product_option_id',

        'value',
        'hex_color',
        'sort_order',
    ];
    public function owner(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(ProductOption::class, 'product_option_id');
    }

    public function variants(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductVariant::class,
            'product_variant_option_value'
        );
    }
}
