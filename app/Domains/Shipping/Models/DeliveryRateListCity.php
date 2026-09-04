<?php

namespace App\Domains\Shipping\Models;

use App\Models\Locations\City;
use App\Models\Locations\State;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Per-municipality (بلديّة) home delivery price scoped to a store price
 * list. Overrides the list's state-level home cost when set.
 */
class DeliveryRateListCity extends Model
{
    use HasUlids;

    protected $fillable = [
        'delivery_price_list_id',
        'state_id',
        'city_id',
        'home_cost',
    ];

    protected $casts = [
        'home_cost' => 'decimal:2',
    ];

    public function priceList(): BelongsTo
    {
        return $this->belongsTo(DeliveryPriceList::class, 'delivery_price_list_id');
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
