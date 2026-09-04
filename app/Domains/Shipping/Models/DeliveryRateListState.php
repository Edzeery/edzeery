<?php

namespace App\Domains\Shipping\Models;

use App\Models\Locations\State;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * State-level home/office delivery prices scoped to a store price list.
 */
class DeliveryRateListState extends Model
{
    use HasUlids;

    protected $fillable = [
        'delivery_price_list_id',
        'state_id',
        'home_cost',
        'office_cost',
    ];

    protected $casts = [
        'home_cost' => 'decimal:2',
        'office_cost' => 'decimal:2',
    ];

    public function priceList(): BelongsTo
    {
        return $this->belongsTo(DeliveryPriceList::class, 'delivery_price_list_id');
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }
}
