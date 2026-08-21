<?php

namespace App\Models\Products;

use App\Models\Category;
use App\Models\InventoryMovement;
use App\Models\Stores\Store;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ProductVariant extends Model
{
    use HasUlids;

    protected static function booted()
    {
        static::creating(function ($model) {
            if ($model->store_id === null) {
                $model->store_id = $model->product?->store_id ?: currentStore()?->id;
            }
        });
    }

    protected $fillable = [
        'product_id',
        'store_id',
        'name',
        'sku',
        'barcode',
        'price',
        'compare_price',
        'cost_price',
        'stock',
        'low_stock_threshold',
        'last_low_stock_notified_at',
        'weight',
        'length',
        'width',
        'height',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'stock' => 'integer',
        'low_stock_threshold' => 'integer',
        'last_low_stock_notified_at' => 'datetime',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'weight' => 'decimal:3',
        'length' => 'decimal:2',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
    ];

    /* ================== Relations ================== */

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function optionValues()
    {
        return $this->belongsToMany(
            ProductOptionValue::class,
            'product_variant_option_value'
        )->withPivot('product_option_id');
    }

    public function images(): MorphMany
    {
        return $this->morphMany(ProductImage::class, 'imageable');
    }

    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class);
    }

    /* Stock helpers */

    public function isOutOfStock(): bool
    {
        return $this->stock <= 0;
    }

    public function isLowStock(): bool
    {
        return $this->stock > 0
            && $this->stock <= $this->low_stock_threshold;
    }

    public function stockStatus(): string
    {
        return match (true) {
            $this->isOutOfStock() => 'out',
            $this->isLowStock() => 'low',
            default => 'in',
        };
    }

    public function primaryImage()
    {
        return $this->morphOne(ProductImage::class, 'imageable')
            ->where('is_primary', true);
    }

    public function shouldNotifyLowStock(): bool
    {
        return $this->isLowStock()
            && $this->last_low_stock_notified_at === null;
    }
}
