<?php

namespace App\Models\Stores;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreSetting extends Model
{
    use HasFactory;
    protected $table = 'store_settings';

    protected $primaryKey = 'store_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'store_id',
        'currency',
        'currency_symbol',
        'language',
        'supported_languages',
        'timezone',
        'phone',
        'guest_checkout',
        'inventory_tracking',
        'show_out_of_stock',
        'allow_backorder',
        'payment_methods',
        'contact_info',
    ];

    protected $casts = [
        'guest_checkout' => 'boolean',
        'inventory_tracking' => 'boolean',
        'show_out_of_stock' => 'boolean',
        'allow_backorder' => 'boolean',
        'payment_methods' => 'array',
        'contact_info' => 'array',
        'supported_languages' => 'array',
    ];

    /* ================= Relations ================= */

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
