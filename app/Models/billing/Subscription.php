<?php

namespace App\Models\billing;

use App\Enums\SubscriptionPayment\StatusSubscriptionEnum;
use App\Models\billing\Payment;
use App\Models\Plans\FeatureConsumption;
use App\Models\Plans\Plan;
use App\Models\Plans\PlanPrice;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscription extends Model
{
    use HasUlids;
    use SoftDeletes;
    protected $fillable = [
        'user_id',
        'plan_id',
        'plan_price_id',
        'was_switched',
        'is_trial',

        'trial_ends_at',
        'starts_at',
        'canceled_at',
        'ends_at',
        'suppressed_at',

        'status',
    ];

    protected $casts = [
        'status' => StatusSubscriptionEnum::class,
        'trial_ends_at' => 'date',
        'starts_at' => 'date',
        'canceled_at' => 'date',
        'ends_at' => 'date',
        'suppressed_at' => 'date',
        'is_trial' => 'boolean',
    ];


    public function user()
    {
        return $this->belongsTo(User::class); // مرتبط بالتاجر وليس المتجر
    }

    // علاقة بالخطة
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function planPrice()
    {
        return $this->belongsTo(PlanPrice::class, 'plan_price_id');
    }


    // علاقة بالمدفوعات
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function renewals()
    {
        return $this->hasMany(SubscriptionRenewal::class);
    }

    // تحقق إذا الاشتراك نشط
    public function isActive(): bool
    {
        return $this->status === 'active'
            && (!$this->ends_at || $this->ends_at->isFuture());
    }

    // لعمل trial افتراضي
    public function onTrial(): bool
    {
        return $this->is_trial
            && now()->lte($this->trial_ends_at);
    }

    public function activeSubscription(): ?Subscription
    {
        return $this->where('status', 'active')
            ->latest('updated_at');
    }


    public function featureConsumptions()
    {
        return $this->hasMany(FeatureConsumption::class);
    }
}
