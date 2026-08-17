<?php

namespace App\Models\Finance;

use App\Enums\Finance\DebtStatusEnum;
use App\Enums\Finance\DebtTypeEnum;
use App\Models\Stores\Store;
use App\Models\User;
use App\Scopes\StoreScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Debt extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        static::addGlobalScope(new StoreScope);
    }

    protected $fillable = [
        'user_id',
        'store_id',
        'type',
        'counterparty_name',
        'total_amount',
        'paid_amount',
        'due_date',
        'status',
        'description',
        'reminder_date',
        'notes',
        'count_at_incurrence',
    ];

    protected function casts(): array
    {
        return [
            'type' => DebtTypeEnum::class,
            'status' => DebtStatusEnum::class,
            'total_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'due_date' => 'date',
            'reminder_date' => 'date',
            'count_at_incurrence' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /** @return HasMany<DebtPayment, $this> */
    public function payments(): HasMany
    {
        return $this->hasMany(DebtPayment::class);
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0, $this->total_amount - $this->paid_amount);
    }

    public function getProgressAttribute(): float
    {
        if ($this->total_amount <= 0) {
            return 0;
        }

        return round(($this->paid_amount / $this->total_amount) * 100, 2);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date && $this->due_date->isPast() && $this->status !== DebtStatusEnum::PAID;
    }

    public function scopeOwed($query)
    {
        return $query->where('type', DebtTypeEnum::OWED);
    }

    public function scopeOwing($query)
    {
        return $query->where('type', DebtTypeEnum::OWING);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [DebtStatusEnum::ACTIVE, DebtStatusEnum::PARTIAL, DebtStatusEnum::OVERDUE]);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', DebtStatusEnum::OVERDUE);
    }
}
