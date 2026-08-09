<?php

namespace App\Models\Locations;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    use HasUlids;

    protected $fillable = [
        'name',
        'arabic_name',
        'code',
        'is_active',
        'is_cod_available',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_cod_available' => 'boolean',
    ];

    /* ================= Relations ================= */

    public function states(): HasMany
    {
        return $this->hasMany(State::class);
    }

    /* ================= Scopes ================= */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeCodAvailable($query)
    {
        return $query->where('is_cod_available', true);
    }
}
