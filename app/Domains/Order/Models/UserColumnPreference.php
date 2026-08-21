<?php

namespace App\Domains\Order\Models;

use App\Models\Stores\Team\StoreMembership;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserColumnPreference extends Model
{
    use HasUlids;

    protected $fillable = [
        'membership_id',
        'view_key',
        'visible_columns',
    ];

    protected $casts = [
        'visible_columns' => 'array',
    ];

    public function membership(): BelongsTo
    {
        return $this->belongsTo(StoreMembership::class, 'membership_id');
    }
}
