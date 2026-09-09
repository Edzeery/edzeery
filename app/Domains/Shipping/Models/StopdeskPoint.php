<?php

namespace App\Domains\Shipping\Models;

use App\Models\Locations\City;
use App\Models\Locations\State;
use App\Models\Stores\Store;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StopdeskPoint extends Model
{
    use HasUlids;

    protected $fillable = [
        'store_id',
        'shipping_provider_id',
        'state_id',
        'city_id',
        'name',
        'address',
        'phone',
        'external_code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
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

    /* =========================
     | Coverage helpers
     ========================= */

    /**
     * Communes (city_ids) actually served by the carrier's active points in a
     * given wilaya. Single shared source for the order form cascade and the
     * inline city editor, so both always scope the selectable communes to the
     * chosen shipping company.
     */
    public static function communitiesCoveredFor(string $storeId, string $providerId, string $stateId): array
    {
        return static::query()
            ->where('store_id', $storeId)
            ->where('shipping_provider_id', $providerId)
            ->where('state_id', $stateId)
            ->where('is_active', true)
            ->whereNotNull('city_id')
            ->distinct()
            ->pluck('city_id')
            ->all();
    }
}
