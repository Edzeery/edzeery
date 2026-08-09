<?php

namespace App\Domains\Billing\Actions;

use App\Domains\Billing\Events\SubscriptionActivated;
use App\Enums\SubscriptionPayment\StatusSubscriptionEnum;
use App\Models\billing\Subscription;

class ActivateSubscriptionAction
{
    public function execute(Subscription $subscription): Subscription
    {
        $subscription->update([
            'status' => StatusSubscriptionEnum::ACTIVE,
        ]);

        event(new SubscriptionActivated($subscription));

        return $subscription->refresh();
    }
}
