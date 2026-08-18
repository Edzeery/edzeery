<?php

namespace App\Models\Orders;

use App\Models\Status;
use App\Models\Stores\Team\StoreMembership;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderStatusHistory extends Model
{
    use HasUlids;

    protected $fillable = [
        'order_id',
        'status_id',
        'changed_by_membership_id',
        'reason',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(StoreMembership::class, 'changed_by_membership_id');
    }
}
