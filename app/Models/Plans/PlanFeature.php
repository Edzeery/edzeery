<?php

namespace App\Models\Plans;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class PlanFeature extends Model
{
    use HasUlids;
    protected $fillable = [
        'name',
        'slug',
        'type',
        'unit',
        'description',
        'consumable',
        'quota',
        'periodicity',
        'periodicity_type',
    ];

    protected $casts = [
        'consumable' => 'boolean',
        'quota' => 'boolean',
        'periodicity' => 'integer',
    ];

    public function plans()
    {
        return $this->belongsToMany(Plan::class, 'plan_plan_feature')
            ->withPivot(['value', 'charges'])
            ->withTimestamps();
    }

    public function isBoolean(): bool
    {
        return $this->type === 'boolean';
    }
}
