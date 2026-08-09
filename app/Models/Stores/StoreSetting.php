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
        'timezone',
        'guest_checkout',
        'inventory_tracking',
        'show_out_of_stock',
        'allow_backorder',
        'contact_info',
    ];

    protected $casts = [
        'guest_checkout' => 'boolean',
        'inventory_tracking' => 'boolean',
        'show_out_of_stock' => 'boolean',
        'allow_backorder' => 'boolean',
        'contact_info' => 'array',
    ];

    /* ================= Relations ================= */

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
