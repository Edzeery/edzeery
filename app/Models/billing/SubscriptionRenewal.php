<?php


namespace App\Models\billing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;

class SubscriptionRenewal extends Model
{
    use HasUlids;

    protected $fillable = [
        'subscription_id',
        'overdue',
        'renewal',
    ];

    protected $casts = [
        'overdue' => 'boolean',
        'renewal' => 'boolean',
    ];

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }
}
