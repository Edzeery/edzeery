<?php

namespace App\Domains\Billing\Services;

use App\Domains\Billing\DTOs\SubscriptionData;
use App\Models\User;
use App\Models\Plans\Plan;
use App\Models\Plans\PlanPrice;

class BillingService
{
    public function __construct(
        protected SubscriptionService $subscriptionService
    ) {}

    public function subscribeUser(
        User $user,
        Plan $plan,
        PlanPrice $price,
        bool $trial = false
    ) {
        $data = SubscriptionData::from($user, $plan, $price, $trial);

        return $this->subscriptionService->createWithPayment($data);
    }
}
