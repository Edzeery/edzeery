<?php

namespace App\Models\Orders;

use App\Domains\Shipping\Models\ShippingProvider;
use App\Enums\Store\OrderTrackingStatus;
use App\Models\Stores\Store;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderTracking extends Model
{
    use HasUlids;

    protected $fillable = [
        'store_id',
        'order_id',
        'shipping_provider_id',
        'tracking_number',
        'carrier_status',
        'carrier_label',
        'tracking_status',
        'shipped_at',
        'delivered_at',
        'returned_at',
        'carrier_raw',
        'last_synced_at',
        'webhook_token',
        'verification_barcode',
        'verified_at',
        'verified_by_membership_id',
        'inspection_result',
        'inspection_notes',
        'processed_at',
        'processed_by_membership_id',
        'requeued_at',
        'requeued_by_membership_id',
        'notes',
    ];

    protected $casts = [
        'shipped_at'      => 'datetime',
        'delivered_at'    => 'datetime',
        'returned_at'     => 'datetime',
        'last_synced_at'  => 'datetime',
        'carrier_raw'     => 'array',
        'verified_at'     => 'datetime',
        'processed_at'    => 'datetime',
        'requeued_at'     => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function histories(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrderTrackingHistory::class, 'order_tracking_id');
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function shippingProvider(): BelongsTo
    {
        return $this->belongsTo(ShippingProvider::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Stores\Team\StoreMembership::class, 'verified_by_membership_id');
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Stores\Team\StoreMembership::class, 'processed_by_membership_id');
    }

    public function requeuedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Stores\Team\StoreMembership::class, 'requeued_by_membership_id');
    }

    public function isDelivered(): bool
    {
        return $this->delivered_at !== null;
    }

    public function isReturned(): bool
    {
        return $this->returned_at !== null;
    }

    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    public function isProcessed(): bool
    {
        return $this->processed_at !== null;
    }

    public function isRequeued(): bool
    {
        return $this->requeued_at !== null;
    }

    /** حالة التتبع الطبيعية (إن وُجدت). */
    public function trackingStatus(): ?OrderTrackingStatus
    {
        return $this->tracking_status
            ? OrderTrackingStatus::tryFrom($this->tracking_status)
            : null;
    }
}
