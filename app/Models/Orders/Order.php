<?php

namespace App\Models\Orders;

use App\Models\Customer;
use App\Models\Status;
use App\Models\Stores\Store;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'number',
        'total_amount',
        'notes',
        'phone_secondary',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function statusModel()
    {
        return Status::query()
            ->where('type', 'order')
            ->where('key', $this->status)
            ->first();
    }

    /* =========================
     | Helpers
     ========================= */
    public function isStatus(string $key): bool
    {
        return $this->status === $key;
    }
}
