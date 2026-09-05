<?php

namespace App\Models\Orders;

use App\Models\Orders\Order;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * سجل أحداث الطلبية (Order Event Log): يعرف المسؤول ماذا حدث للطلب —
 * إنشاء، تعديل حقول مفتاحية، حالة، اتصالات، إرسال لشركة التوصيل، أحداث تتبع، إسناد...
 */
class OrderEvent extends Model
{
    use HasUlids;

    public const ACTOR_MEMBERSHIP = 'membership';
    public const ACTOR_SYSTEM = 'system';

    protected $fillable = [
        'store_id',
        'order_id',
        'actor_membership_id',
        'actor_type',
        'event_type',
        'message',
        'payload',
        'occurred_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'occurred_at' => 'datetime',
    ];

    public $timestamps = false;

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(StoreMembership::class, 'actor_membership_id');
    }
}