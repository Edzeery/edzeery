<?php

namespace App\Models\billing;

use App\Enums\SubscriptionPayment\StatusPaymentEnum;
use App\Models\Plans\Plan;
use App\Models\Plans\PlanPrice;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasUlids;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'store_id',
        'subscription_id',
        'plan_price_id',
        'gateway',
        'transaction_id',
        'status',
        'amount',
        'currency',
        'meta',
        'paid_at',
        'manual_method',
        'reference_number',
        'proof_file_path',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
    ];
    protected $casts = [
        'status' => StatusPaymentEnum::class,
        'meta' => 'array',
        'paid_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    // علاقة بالمتجر
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // علاقة بالاشتراك
    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    // علاقة بالمتجر
    public function store()
    {
        return $this->belongsTo(\App\Models\Stores\Store::class);
    }

    // علاقة بالخطة (وقت الدفع)

    public function planPrice()
    {
        return $this->belongsTo(PlanPrice::class);
    }
    // تحقق إذا الدفع مكتمل
    public function isPaid(): bool
    {
        return $this->status === StatusPaymentEnum::PAID && $this->paid_at !== null;
    }

    public function isPendingReview(): bool
    {
        return $this->status === StatusPaymentEnum::PENDING_REVIEW;
    }

    public function reviewer()
    {
        return $this->belongsTo(\App\Models\User::class, 'reviewed_by');
    }
}
