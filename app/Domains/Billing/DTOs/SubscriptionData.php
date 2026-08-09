<?php

namespace App\Domains\Billing\DTOs;

use App\Models\User;
use App\Models\Plans\Plan;
use App\Models\Plans\PlanPrice;

class SubscriptionData
{
    public function __construct(
        public User $user,
        public Plan $plan,
        public PlanPrice $planPrice,
        public bool $isTrial = false,
        public ?int $trialDays = null,
    ) {}

    public static function from(User $user, Plan $plan, PlanPrice $planPrice, bool $isTrial = false): self
    {
        return new self(
            user: $user,
            plan: $plan,
            planPrice: $planPrice,
            isTrial: $isTrial,
            trialDays: $plan->trial_days
        );
    }
}
