<?php

namespace App\Domains\Shipping\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Carrier extends Model
{
    use HasUlids;

    protected $fillable = [
        'platform_id',
        'name',
        'code',
        'logo',
        'credential_fields',
        'is_active',
        'supports_delivery',
        'supports_exchange',
        'supports_pickup',
        'supports_free_shipping_mode',
        'supports_express_economic',
        'supports_api_notes',
        'supports_order_delete',
        'supports_price_sync',
        'sort_order',
    ];

    protected $casts = [
        'credential_fields'             => 'array',
        'is_active'                     => 'boolean',
        'supports_delivery'             => 'boolean',
        'supports_exchange'             => 'boolean',
        'supports_pickup'               => 'boolean',
        'supports_free_shipping_mode'   => 'boolean',
        'supports_express_economic'     => 'boolean',
        'supports_api_notes'            => 'boolean',
        'supports_order_delete'         => 'boolean',
        'supports_price_sync'           => 'boolean',
        'sort_order'                    => 'integer',
    ];

    public function platform(): BelongsTo
    {
        return $this->belongsTo(CarrierPlatform::class, 'platform_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Normalised list of credential fields defined for this carrier,
     * each as ['key' => ..., 'label' => ..., 'type' => 'text'|'password', 'required' => bool].
     */
    public function credentialFieldList(): array
    {
        return is_array($this->credential_fields)
            ? array_values($this->credential_fields)
            : [];
    }

    /**
     * Normalised capability flags keyed by a stable short name. Single source
     * of truth reused by the merchant Blade view and the Filament admin, so
     * raw column-name strings never leak across layers.
     *
     * @return array{delivery: bool, exchange: bool, pickup: bool, free_shipping_mode: bool, express_economic: bool, api_notes: bool, order_delete: bool, price_sync: bool}
     */
    public function capabilityList(): array
    {
        return [
            'delivery'           => (bool) $this->supports_delivery,
            'exchange'           => (bool) $this->supports_exchange,
            'pickup'             => (bool) $this->supports_pickup,
            'free_shipping_mode' => (bool) $this->supports_free_shipping_mode,
            'express_economic'   => (bool) $this->supports_express_economic,
            'api_notes'          => (bool) $this->supports_api_notes,
            'order_delete'       => (bool) $this->supports_order_delete,
            'price_sync'         => (bool) $this->supports_price_sync,
        ];
    }
}
