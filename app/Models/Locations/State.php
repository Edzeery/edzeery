<?php

namespace App\Models\Locations;
 
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class State extends Model
{
    use HasUlids;
    protected $fillable = [
        'country_id',
        'state_code',
        'name',
        'arabic_name',
        'is_active',
        'is_cod_available',
        'sort_order',
        'longitude',
        'latitude',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_cod_available' => 'boolean',
        'longitude' => 'decimal:6',
        'latitude' => 'decimal:6',
    ];

    /* ================= Relations ================= */


    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
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
