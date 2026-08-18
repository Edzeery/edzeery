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
        'credentials',
        'is_active',
        'is_default',
        'flat_rate',
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

    public function stopdeskPoints(): HasMany
    {
        return $this->hasMany(StopdeskPoint::class);
    }
}
