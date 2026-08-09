<?php

namespace App\Domains\Billing\Actions;

use App\Models\billing\Subscription;
use App\Models\billing\SubscriptionRenewal;

class RenewSubscriptionAction
{
    public function execute(Subscription $subscription): Subscription
    {
        $newEnd = $subscription->planPrice->endsAt($subscription->ends_at);

        $subscription->update([
            'starts_at' => $subscription->ends_at,
            'ends_at'   => $newEnd,
        ]);

        SubscriptionRenewal::create([
            'subscription_id' => $subscription->id,
            'overdue'         => false,
            'renewal'         => true,
        ]);

        return $subscription->refresh();
    }
}
