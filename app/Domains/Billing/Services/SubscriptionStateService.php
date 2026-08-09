<?php

namespace App\Domains\Billing\Services;

use App\Enums\SubscriptionPayment\StatusSubscriptionEnum;
use App\Models\billing\Subscription;

class SubscriptionStateService
{
    public function transition(
        Subscription $subscription,
        StatusSubscriptionEnum $to
    ): Subscription {

        $this->guardTransition($subscription->status, $to);

        $subscription->update([
            'status' => $to,
        ]);

        event(new SubscriptionStatusChanged($subscription));

        return $subscription;
    }
}
