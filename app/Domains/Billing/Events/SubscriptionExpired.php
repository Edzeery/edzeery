<?php

namespace App\Domains\Billing\Events;

use App\Models\billing\Subscription;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SubscriptionExpired
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Subscription $subscription
    ) {}
}
