<?php

namespace App\Domains\Billing\Listeners;

use App\Domains\Billing\Actions\ActivateSubscriptionAction;
use App\Domains\Billing\Events\PaymentSucceeded;

class ActivateSubscriptionOnPaymentSucceeded
{
    public function handle(PaymentSucceeded $event): void
    {
        $subscription = $event->payment->subscription;

        if (! $subscription) {
            return;
        }

        app(ActivateSubscriptionAction::class)->execute($subscription);
    }
}
