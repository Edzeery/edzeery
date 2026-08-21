<?php

namespace App\Domains\Order\Models;

use App\Models\Stores\Store;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConfirmationShift extends Model
{
    use HasUlids;

    protected $fillable = [
        'store_id',
        'membership_id',
        'shift_type',
        'start_time',
        'end_time',
        'days_of_week',
        'is_active',
    ];

    protected $casts = [
        'days_of_week' => 'array',
        'is_active' => 'boolean',
        'start_time' => 'string',
        'end_time' => 'string',
    ];

    public function membership(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Stores\Team\StoreMembership::class, 'membership_id');
    }
}
