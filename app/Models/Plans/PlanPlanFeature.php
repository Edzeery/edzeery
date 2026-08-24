<?php

namespace App\Models\Plans;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PlanPlanFeature extends Pivot
{
    protected $casts = [
        'value' => 'string', // Stored as string in DB, handled in getFeatureValue
        'charges' => 'decimal:2',
    ];

    public function getValueAttribute($value)
    {
        // Try to decode as JSON first, fallback to string
        if (is_string($value) && (str_starts_with($value, '{') || str_starts_with($value, '['))) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }
        return $value;
    }

    public function setValueAttribute($value)
    {
        if (is_array($value) || is_object($value)) {
            $this->attributes['value'] = json_encode($value);
        } else {
            $this->attributes['value'] = $value;
        }
    }
}