<?php

namespace App\Models\Orders;

use App\Models\Orders\Order;
use App\Models\Orders\OrderTracking;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * سجل حالات التتبع لكل شحنة — تاريخ كامل (مُرسل ← في الطريق ← قيد التوصيل ← سلّم/مرتجع).
 */
class OrderTrackingHistory extends Model
{
    use HasUlids;

    protected $fillable = [
        'store_id',
        'order_id',
        'order_tracking_id',
        'status',
        'changed_by_membership_id',
        'notes',
        'payload',
        'created_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'created_at' => 'datetime',
    ];

    public const UPDATED_AT = null;

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function tracking(): BelongsTo
    {
        return $this->belongsTo(OrderTracking::class, 'order_tracking_id');
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(StoreMembership::class, 'changed_by_membership_id');
    }
}