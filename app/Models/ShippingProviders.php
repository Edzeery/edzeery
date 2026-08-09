<?php

namespace App\Models;

use App\Models\Stores\Store;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingProviders extends Model
{
    use HasUlids;
    protected $fillable = [
        'store_id',
        'name',
        'credentials',          // ALWAYS positive
        'is_active',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
