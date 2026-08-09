<?php

namespace App\Models\Stores;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreSeo extends Model
{
    use HasFactory;
    protected $primaryKey = 'store_id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'store_seo';

    protected $fillable = [
        'store_id',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_image',
    ];

    /* ================= Relations ================= */

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
