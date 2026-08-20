<?php

namespace App\Models\Orders;

use App\Models\Customer;
use App\Models\Locations\City;
use App\Models\Locations\State;
use App\Models\Status;
use App\Models\Stores\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasUlids;
    use SoftDeletes;

    protected $fillable = [
        'store_id',
        'user_id',
        'customer_id',
        'status_id',
        'created_by_membership_id',
        'number',
        'total_amount',
        'state_id',
        'city_id',
        'stopdesk_point_id',
        'address',
        'delivery_type',
        'payment_method',
        'shipping_cost',
        'notes',
        'phone_secondary',
    ];

    protected $casts = [
        'total_amount'  => 'decimal:2',
        'shipping_cost' => 'decimal:2',
    ];

    const DELIVERY_HOME    = 'home';
    const DELIVERY_STOPDESK = 'stopdesk';

    /* =========================
     | Relationships
     ========================= */

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function stopdeskPoint(): BelongsTo
    {
        return $this->belongsTo(\App\Domains\Shipping\Models\StopdeskPoint::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function latestStatusHistory()
    {
        return $this->hasOne(OrderStatusHistory::class)->latestOfMany('created_at');
    }

    /* =========================
     | Accessors
     ========================= */

    public function getStatusKeyAttribute(): ?string
    {
        return $this->status?->key;
    }

    /* =========================
     | Helpers
     ========================= */

    public function isStatus(string $key): bool
    {
        return $this->status?->key === $key;
    }

    public function createdByMembership()
    {
        return $this->belongsTo(\App\Models\Stores\Team\StoreMembership::class, 'created_by_membership_id');
    }

    public function nextOrderNumber(): string
    {
        $lastNumber = self::withTrashed()
            ->where('store_id', $this->store_id)
            ->max('number');

        $nextNumber = $lastNumber ? (int) $lastNumber + 1 : 1;

        return str_pad((string) $nextNumber, 5, '0', STR_PAD_LEFT);
    }
}
