<?php

namespace App\Models\Products;

use App\Enums\Store\ProductOptionInputType;
use App\Models\Stores\Store;
use App\Models\Traits\HasOptionValues;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductOption extends Model
{
     use HasUlids;
    use HasOptionValues;

    protected $fillable = [
        'store_id',
        'team_id',
        'name',
        'type',
        'sort_order',
    ];


    protected $casts = [
        'type' => ProductOptionInputType::class,
    ];

   public function owner(): BelongsTo
    {
        return $this->belongsTo(Store::class,'store_id');
    }
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(ProductOptionValue::class);
    }
    public function isUsedInVariants(): bool
    {
        return $this->values()
            ->whereHas('variants')
            ->exists();
    }
}
