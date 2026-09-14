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
        'shipment_types_enabled',
        'refund_request_enabled',
        'can_open_enabled',
        'send_from_carrier_warehouse_enabled',
        'is_active',
        'is_default',
        'max_weight_kg',
        'webhook_token',
        'webhook_last_seen_at',
        'flat_rate',
        'rider_name',
        'rider_phone',
    ];

    protected $casts = [
        'credentials'             => 'array',
        'shipment_types_enabled'  => 'array',
        'refund_request_enabled'  => 'boolean',
        'can_open_enabled'        => 'boolean',
        'send_from_carrier_warehouse_enabled' => 'boolean',
        'is_active'               => 'boolean',
        'is_default'              => 'boolean',
        'max_weight_kg'           => 'decimal:2',
        'flat_rate'               => 'decimal:2',
        'webhook_last_seen_at'    => 'datetime',
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
