<?php

namespace App\Domains\Shipping\Models;

use App\Models\Products\Product;
use App\Models\Stores\Store;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Store-wide price list (قائمة أسعار) — not tied to a carrier. Products
 * attached to the list share the same announced delivery prices.
 */
class DeliveryPriceList extends Model
{
    use HasUlids;

    protected $fillable = [
        'store_id',
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'delivery_price_list_products')
            ->withTimestamps();
    }

    public function stateRates(): HasMany
    {
        return $this->hasMany(DeliveryRateListState::class);
    }

    public function cityRates(): HasMany
    {
        return $this->hasMany(DeliveryRateListCity::class);
    }
}
