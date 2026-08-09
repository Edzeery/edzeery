<?php

namespace App\Models;

use App\Models\Stores\Store;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;

class Status extends Model
{
    use HasUlids;
    protected $fillable = [
        'store_id',
        'type',
        'key',
        'label',
        'color',
        'is_system',
        'affects_inventory',
        'movement_type',
        'sort_order',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'affects_inventory' => 'boolean',
    ];

    /* =========================
     | Relationships
     ========================= */

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /* =========================
     | Scopes
     ========================= */

    // Statuses التي أنشأها النظام فقط
    public function scopeSystem(Builder $query): Builder
    {
        return $query->where('is_system', true);
    }

    // Statuses الخاصة بتاجر
    public function scopeForStore(Builder $query, int $storeId): Builder
    {
        return $query->where('store_id', $storeId);
    }

    // حسب النوع (order / shipment / payment ...)
    public function scopeForType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }
}
