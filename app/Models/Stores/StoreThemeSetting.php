<?php

namespace App\Models\Stores;

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

    /* ================= Relations ================= */

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
