<?php

namespace App\Domains\Shipping\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Carrier extends Model
{
    use HasUlids;

    protected $fillable = [
        'platform_id',
        'name',
        'code',
        'credential_fields',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'credential_fields' => 'array',
        'is_active'         => 'boolean',
        'sort_order'        => 'integer',
    ];

    public function platform(): BelongsTo
    {
        return $this->belongsTo(CarrierPlatform::class, 'platform_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Normalised list of credential fields defined for this carrier,
     * each as ['key' => ..., 'label' => ..., 'type' => 'text'|'password', 'required' => bool].
     */
    public function credentialFieldList(): array
    {
        return is_array($this->credential_fields)
            ? array_values($this->credential_fields)
            : [];
    }
}
