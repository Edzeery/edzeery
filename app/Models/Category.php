<?php

namespace App\Models;

use App\Models\Products\Product;
use App\Models\Stores\Store;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasUlids;
    use SoftDeletes;
    protected $fillable = [
        'store_id',
        'parent_id', // ✅ أضفناه
        'name',
        'slug',
        'logo',
        'is_active',
    ];

    /* ================= Relations ================= */

    // المتجر الذي تنتمي له الفئة
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    // المنتجات المرتبطة بهذه الفئة
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    // الفئة الأب (إذا كانت موجودة)
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    // الفئات الفرعية
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function getFullNameAttribute(): string
    {
        $names = [$this->name];
        $parent = $this->parent;
        while ($parent) {
            array_unshift($names, $parent->name);
            $parent = $parent->parent;
        }
        return implode(' > ', $names);
    }
}
