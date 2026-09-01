<?php

namespace App\Domains\Shipping\Models;

use App\Models\Stores\Store;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShippingProvider extends Model
{
    use HasUlids;
    use SoftDeletes;

    protected $fillable = [
        'store_id',
        'name',
        'code',
        'carrier_platform_id',
        'carrier_id',
        'credentials',
        'is_active',
        'is_default',
        'flat_rate',
        'rider_name',
        'rider_phone',
    ];

    protected $casts = [
        'credentials' => 'array',
        'is_active'   => 'boolean',
        'is_default'  => 'boolean',
        'flat_rate'   => 'decimal:2',
    ];

    /* =========================
     | Relationships
     ========================= */

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function rates(): HasMany
    {
        return $this->hasMany(ShippingRate::class);
    }

    public function deliveryRates(): HasMany
    {
        return $this->hasMany(DeliveryRate::class, 'shipping_provider_id');
    }

    public function carrierPlatform(): BelongsTo
    {
        return $this->belongsTo(CarrierPlatform::class, 'carrier_platform_id');
    }

    public function carrier(): BelongsTo
    {
        return $this->belongsTo(Carrier::class, 'carrier_id');
    }

    public function stopdeskPoints(): HasMany
    {
        return $this->hasMany(StopdeskPoint::class);
    }
}
