<?php

namespace App\Models\Products;

use App\Models\Stores\Store;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ProductImage extends Model
{
    use HasUlids;
    protected $fillable = [
        'path',
        'store_id',
        'sort_order',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function (self $image) {
            if (blank($image->store_id) && $image->imageable) {
                $image->store_id = $image->imageable->store_id;
            }
        });
    }

    public function imageable(): MorphTo
    {
        return $this->morphTo();
    }
    public function owner(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
