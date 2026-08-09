<?php

namespace App\Models\Plans;

use Illuminate\Database\Eloquent\Model;

class FeatureConsumption extends Model
{
    protected $fillable = [
        'subscription_id',
        'plan_feature_id',
        'consumption',
        'expired_at',
    ];

    protected $casts = [
        'consumption' => 'decimal:2',
        'expired_at' => 'datetime',
    ];

    public function feature()
    {
        return $this->belongsTo(PlanFeature::class, 'plan_feature_id');
    }

    public function subscription()
    {
        return $this->belongsTo(\App\Models\billing\Subscription::class);
    }
}
