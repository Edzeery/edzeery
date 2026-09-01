<?php

namespace App\Domains\Shipping\Models;

use App\Models\Orders\Order;
use App\Models\Stores\Store;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryRider extends Model
{
    use HasUlids;
    use SoftDeletes;

    public const VEHICLE_MOTORCYCLE = 'motorcycle';
    public const VEHICLE_CAR = 'car';
    public const VEHICLE_BICYCLE = 'bicycle';
    public const VEHICLE_VAN = 'van';

    protected $fillable = [
        'store_id',
        'name',
        'phone',
        'email',
        'vehicle_type',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $appends = ['vehicle_label'];

    /* =========================
     | Relationships
     ========================= */

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /* =========================
     | Scopes
     ========================= */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForStore(Builder $query, ?string $storeId = null): Builder
    {
        return $query->where('store_id', $storeId ?? (function_exists('currentStoreId') ? currentStoreId() : null));
    }

    /* =========================
     | Helpers
     ========================= */

    public static function vehicleOptions(): array
    {
        return [
            self::VEHICLE_MOTORCYCLE => __('merchant_panel.vehicle_motorcycle'),
            self::VEHICLE_CAR => __('merchant_panel.vehicle_car'),
            self::VEHICLE_BICYCLE => __('merchant_panel.vehicle_bicycle'),
            self::VEHICLE_VAN => __('merchant_panel.vehicle_van'),
        ];
    }

    public function vehicleLabel(): string
    {
        $options = self::vehicleOptions();

        return $options[$this->vehicle_type] ?? $this->vehicle_type;
    }

    public function getVehicleLabelAttribute(): ?string
    {
        return $this->vehicleLabel();
    }
}
