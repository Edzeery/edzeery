<?php

namespace App\Models\Stores;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

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
        static::saving(function (self $model) {
            if ($model->isDirty('section_content')) {
                $content = $model->section_content;
                if (is_array($content)) {
                    $allowedSections = ['hero', 'social_proof', 'faq', 'cta', 'categories', 'brands', 'description'];
                    foreach ($content as $section => $data) {
                        if (! in_array($section, $allowedSections)) {
                            throw ValidationException::withMessages([
                                'section_content' => "Invalid section '{$section}' in section_content",
                            ]);
                        }
                        if (! is_array($data)) {
                            throw ValidationException::withMessages([
                                'section_content' => "Section '{$section}' content must be an array",
                            ]);
                        }
                    }
                }
            }
        });
    }

    /* ================= Relations ================= */

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
