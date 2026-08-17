<?php

namespace App\Models\Plans;

use App\Models\billing\Subscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
    use HasUlids;
    use SoftDeletes;
    protected $fillable = [
        'name',
        'slug',
        'description',
        'trial_days',
        'max_stores',
        'upgrade_to_plan_id',
        'currency',
        'is_active',
        'is_default',
        'is_custom',
        'assigned_to_user_id',
    ];

    /* ================= Relations ================= */

    public function features()
    {
        return $this->belongsToMany(PlanFeature::class, 'plan_plan_feature')
            ->withPivot('value')
            ->withTimestamps();
    }



    public function staffLimit(): ?int
    {
        $feature = $this->features->firstWhere('slug', 'staff_limit');

        if (!$feature) {
            return null;
        }

        if ($feature->pivot->value === 'unlimited') {
            return null; // null = unlimited
        }

        return (int) $feature->pivot->value;
    }

    public function upgradePlan()
    {
        return $this->belongsTo(Plan::class, 'upgrade_to_plan_id');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function prices()
    {
        return $this->hasMany(PlanPrice::class);
    }


    /* ================= Helpers ================= */

    public function priceFor(string $period): ?PlanPrice
    {
        return $this->prices->firstWhere('billing_period', $period);
    }

    public function getFeatureValue(string $slug): mixed
    {
        $feature = $this->features->firstWhere('slug', $slug);
        return $feature?->pivot?->value;
    }
    public function getIsTrialAttribute(): bool
    {
        return $this->trial_days > 0;
    }

    public function hasUnlimitedFeature(string $slug): bool
    {
        return $this->getFeatureValue($slug) === 'unlimited';
    }

    public function scopePublic($query)
    {
        return $query->where('is_custom', false);
    }
}
