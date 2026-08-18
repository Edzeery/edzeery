<?php

namespace App\Domains\Shipping\Models;

use App\Models\Locations\City;
use App\Models\Locations\State;
use App\Models\Stores\Store;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingRate extends Model
{
    use HasUlids;

    protected $fillable = [
        'store_id',
        'shipping_provider_id',
        'state_id',
        'city_id',
        'label',
        'cost',
        'free_above',
        'is_active',
    ];

    protected $casts = [
        'cost'       => 'decimal:2',
        'free_above' => 'integer',
        'is_active'  => 'boolean',
    ];

    /* =========================
     | Relationships
     ========================= */

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(ShippingProvider::class, 'shipping_provider_id');
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }
}
