<?php

namespace App\Models;

use App\Enums\Store\InventoryMovementType;
use App\Models\Products\ProductVariant;
use App\Models\Stores\Store;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryMovement extends Model
{
    use HasUlids;
    use SoftDeletes;
    protected $fillable = [
        'store_id',
        'product_variant_id',
        'quantity',          // ALWAYS positive
        'balance_after',
        'type',
        'user_id',
        'source_type',
        'source_id',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'balance_after' => 'integer',
        'type' => InventoryMovementType::class,
    ];


    /* ================== Relations ================== */

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function source()
    {
        return $this->morphTo();
    }

    /* ================== Helpers ================== */

    public function signedQuantity(): int
    {
        return $this->type->isDecrease()
            ? -$this->quantity
            : $this->quantity;
    }
}
