<?php

namespace App\Models\Locations;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class City extends Model
{
     use HasUlids;
    protected $fillable = [
        'state_id',
        'name',
        'arabic_name',
        'post_code',
        'city_code',
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


    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function country(): BelongsTo
    {
        return $this->state->country();
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
