<?php

namespace App\Domains\Billing\Actions;

use App\Domains\Billing\DTOs\SubscriptionData;
use App\Domains\Billing\Events\SubscriptionCreated;
use App\Enums\SubscriptionPayment\StatusSubscriptionEnum;
use App\Models\billing\Subscription;
use Illuminate\Support\Carbon;

class CreateSubscriptionAction
{
    public function execute(SubscriptionData $data): Subscription
    {
        $startDate = now();

        $endsAt = $data->planPrice->endsAt($startDate);

        $subscription = Subscription::create([
            'user_id'        => $data->user->id,
            'plan_id'        => $data->plan->id,
            'plan_price_id'  => $data->planPrice->id,
            'is_trial'       => $data->isTrial,
            'trial_ends_at'  => $data->isTrial
                ? now()->addDays($data->trialDays)
                : null,
            'starts_at'      => $startDate,
            'ends_at'        => $endsAt,
            'status'         => $data->isTrial
                ? StatusSubscriptionEnum::PENDING
                : StatusSubscriptionEnum::ACTIVE,
        ]);
        event(new SubscriptionCreated($subscription));
        return  $subscription;
    }
}
