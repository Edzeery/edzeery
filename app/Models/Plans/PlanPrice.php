<?php

namespace App\Models\Plans;

use App\Models\billing\Payment;
use App\Models\billing\Subscription;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class PlanPrice extends Model
{
    use HasUlids;
    protected $fillable = [
        'plan_id',
        'billing_period',
        'price',
        'duration',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'duration' => 'integer',
    ];

    /* ================= Relations ================= */

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }


    /* ================= Helpers ================= */

    public function isMonthly(): bool
    {
        return $this->billing_period === 'monthly';
    }

    public function isYearly(): bool
    {
        return $this->billing_period === 'yearly';
    }

    public function endsAt(?Carbon $start = null): Carbon
    {
        return ($start ?? now())->addDays($this->duration);
    }
}
