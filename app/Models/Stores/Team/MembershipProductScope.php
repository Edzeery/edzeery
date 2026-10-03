<?php

namespace App\Models\Stores\Team;

use App\Models\Products\Product;
use App\Models\Stores\Store;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A product visibility restriction carried by a membership.
 *
 * Deliberately NOT the same row as ConfirmationProductAssignment: that table
 * answers "who is the specialist for this product" (auto-assign priority),
 * this one answers "which products may this member see" (HasVisibilityScope).
 * See StoreProductScopeService for the read/write API.
 */
class MembershipProductScope extends Model
{
    use HasUlids;

    protected $fillable = [
        'store_id',
        'membership_id',
        'product_id',
        'created_by_membership_id',
    ];

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
