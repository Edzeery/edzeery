<?php

namespace App\Domains\Shipping\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CarrierPlatform extends Model
{
    use HasUlids;

    protected $fillable = [
        'name',
        'slug',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function carriers(): HasMany
    {
        return $this->hasMany(Carrier::class, 'platform_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
