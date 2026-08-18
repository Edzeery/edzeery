<?php

namespace App\Models\Products;

use App\Models\Brand;
use App\Models\Category;
use App\Models\CategoryProduct;
use App\Models\Stores\Store;
use App\Scopes\StoreScope;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasUlids;
    use SoftDeletes;
    protected $fillable = [
        'store_id',
        'brand_id',
        'primary_category_id',
        'name',
        'slug',
        'sku',
        'barcode',
        'type',
        'short_description',
        'description',
        'price',
        'cost_price',
        'unit',
        'meta_title',
        'meta_description',
        'is_active',
        'is_featured',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    /* Relations */
    protected static function booted()
    {
        static::addGlobalScope(new StoreScope());
    }

    // المتجر الرئيسي للمنتج
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    // الماركة
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function stores(): BelongsToMany
    {
        return $this->belongsToMany(Store::class);
    }

    // الفئة الرئيسية (Primary Category)
    public function primaryCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'primary_category_id');
    }

    // جميع الفئات (إذا أردنا إضافات multi-categories لاحقًا)


    public function categories()
    {
        return $this->belongsToMany(Category::class)
            ->using(CategoryProduct::class)
            ->withPivot('store_id');
    }


    public function images(): MorphMany
    {
        return $this->morphMany(ProductImage::class, 'imageable')
            ->orderBy('sort_order');
    }


    // 👑 الصورة الأساسية
    public function primaryImage(): MorphOne
    {
        return $this->morphOne(ProductImage::class, 'imageable')
            ->orderBy('sort_order');
    }

    // Helper اختياري
    public function getPrimaryImagePathAttribute(): ?string
    {
        return $this->primaryImage?->path;
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }
    /* ================== Helpers ================== */

    public function hasVariants(): bool
    {
        return  $this->type === 'variable';
    }

    // Optional: إظهار اسم الفئة الرئيسية مع الهيراركية
    public function getPrimaryCategoryPathAttribute(): ?string
    {
        return $this->primaryCategory?->full_name;
    }

    public function getMinPriceAttribute(): float
    {
        return $this->variants()->min('price') ?? (float) $this->price;
    }
}
