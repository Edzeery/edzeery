<?php

namespace App\Models\Orders;

use App\Domains\Shipping\Models\DeliveryRider;
use App\Domains\Shipping\Models\ShippingProvider;
use App\Domains\Shipping\Models\StopdeskPoint;
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
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    use HasUlids;
    use SoftDeletes;

    /** @var array<int, array{changed_by_membership_id: ?string, reason: ?string, from_key: ?string}> */
    private static array $transitionMeta = [];

    public static function setTransitionMeta(int|string $orderId, ?string $changedByMembershipId, ?string $reason, ?string $fromKey = null): void
    {
        self::$transitionMeta[$orderId] = [
            'changed_by_membership_id' => $changedByMembershipId,
            'reason' => $reason,
            'from_key' => $fromKey,
        ];
    }

    public static function popTransitionMeta(int|string $orderId): ?array
    {
        $meta = self::$transitionMeta[$orderId] ?? null;
        unset(self::$transitionMeta[$orderId]);
        return $meta;
    }

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
        'shipping_provider_id',
        'delivery_rider_id',
        'address',
        'delivery_type',
        'payment_method',
        'shipping_cost',
        'discount_type',
        'discount_value',
        'discount_reason',
        'notes',
        'phone_secondary',
        'assigned_to_membership_id',
        'assigned_at',
        'assignment_method',
        'assigned_by_membership_id',
        'confirmation_attempts',
        'last_contact_at',
        'weight_kg',
        'shipment_type',
        'meta',
        'send_from_carrier_warehouse',
    ];

    protected $casts = [
        'total_amount'  => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'weight_kg' => 'decimal:2',
        'assigned_at' => 'datetime',
        'last_contact_at' => 'datetime',
        'confirmation_attempts' => 'integer',
        'meta' => 'array',
        'send_from_carrier_warehouse' => 'boolean',
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
        return $this->belongsTo(StopdeskPoint::class);
    }

    public function shippingProvider(): BelongsTo
    {
        return $this->belongsTo(ShippingProvider::class);
    }

    public function deliveryRider(): BelongsTo
    {
        return $this->belongsTo(DeliveryRider::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function latestStatusHistory()
    {
        return $this->hasOne(OrderStatusHistory::class)->latestOfMany('created_at');
    }

    public function trackings(): HasMany
    {
        return $this->hasMany(OrderTracking::class);
    }

    public function latestTracking()
    {
        return $this->hasOne(OrderTracking::class)->latestOfMany('created_at');
    }

    public function confirmedByHistory(): HasOne
    {
        return $this->hasOne(OrderStatusHistory::class)
            ->whereHas('status', fn ($q) => $q->where('key', 'confirmed'))
            ->latestOfMany('created_at');
    }

    public function events(): HasMany
    {
        return $this->hasMany(OrderEvent::class);
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

    public function getDiscountAmountAttribute(): float
    {
        if (! $this->discount_type || ! $this->discount_value) {
            return 0;
        }

        return match ($this->discount_type) {
            'amount' => (float) $this->discount_value,
            'percent' => round((float) $this->total_amount * (float) $this->discount_value / 100, 2),
            default => 0,
        };
    }

    public function getGrandTotalAttribute(): float
    {
        return (float) $this->total_amount - $this->discount_amount;
    }

    public function createdByMembership()
    {
        return $this->belongsTo(\App\Models\Stores\Team\StoreMembership::class, 'created_by_membership_id');
    }

    public function assignedMembership(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Stores\Team\StoreMembership::class, 'assigned_to_membership_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Stores\Team\StoreMembership::class, 'assigned_by_membership_id');
    }

    public function nextOrderNumber(): string
    {
        $lastNumber = self::withTrashed()
            ->where('store_id', $this->store_id)
            ->lockForUpdate()
            ->max(DB::raw('CAST(number AS UNSIGNED)'));

        $nextNumber = $lastNumber ? ((int) $lastNumber + 1) : 1;

        return str_pad((string) $nextNumber, 5, '0', STR_PAD_LEFT);
    }
}
