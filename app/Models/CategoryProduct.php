<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\Pivot;

class CategoryProduct extends Pivot
{
    use HasUlids;
    protected $table = 'category_product';

    protected static function booted()
    {
        static::creating(function ($model) {
            if (! $model->store_id && currentStore()) {
                $model->store_id = currentStore()->id;
            }
        });
    }
}
