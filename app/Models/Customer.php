<?php

namespace App\Models;

use App\Models\Orders\Order;
use App\Models\Stores\Store;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
      use HasUlids;
    use SoftDeletes;
    protected $fillable = [
        'store_id',
        'name',
        'phone',
        'email',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
