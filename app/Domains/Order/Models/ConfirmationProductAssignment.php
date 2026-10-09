<?php

namespace App\Domains\Order\Models;

use App\Models\Products\Product;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConfirmationProductAssignment extends Model
{
    use HasUlids;

    protected $fillable = [
        'store_id',
        'membership_id',
        'product_id',
        'role_scope',
    ];

    protected $attributes = [
        'role_scope' => 'confirm',
    ];

    protected $casts = [
        'role_scope' => 'string',
    ];

    public function scopeConfirm(Builder $query): Builder
    {
        return $query->where('role_scope', 'confirm');
    }

    public function scopeTrack(Builder $query): Builder
    {
        return $query->where('role_scope', 'track');
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function membership(): BelongsTo
    {
        return $this->belongsTo(StoreMembership::class, 'membership_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
