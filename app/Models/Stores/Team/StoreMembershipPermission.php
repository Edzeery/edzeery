<?php

namespace App\Models\Stores\Team;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreMembershipPermission extends Model
{
    use HasUlids;

    protected $fillable = [
        'membership_id',
        'permission',
    ];

    public function membership(): BelongsTo
    {
        return $this->belongsTo(StoreMembership::class, 'membership_id');
    }
}
