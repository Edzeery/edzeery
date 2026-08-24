<?php

namespace App\Models\Stores;

use App\Support\Storefront\StorefrontSections;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreThemeSetting extends Model
{

    use HasFactory;
    protected $primaryKey = 'store_id';
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'store_id',
        'primary_color',
        'secondary_color',
        'font_family',
        'homepage_sections',
        'section_content',
    ];

    protected $casts = [
        'homepage_sections' => 'array',
        'section_content'   => 'array',
    ];

    protected static function booted(): void
    {
        // Last-resort guardrail: delegates all rules to the shared
        // StorefrontSections contract (single source of truth).
        static::saving(function (self $model) {
            if ($model->isDirty(['primary_color', 'secondary_color', 'font_family', 'homepage_sections', 'section_content'])) {
                StorefrontSections::assertValidThemeData($model->only([
                    'primary_color',
                    'secondary_color',
                    'font_family',
                    'homepage_sections',
                    'section_content',
                ]));
            }
        });
    }

    /* ================= Relations ================= */

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
