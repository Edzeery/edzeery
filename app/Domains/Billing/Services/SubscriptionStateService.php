<?php

namespace App\Domains\Billing\Services;

use App\Enums\SubscriptionPayment\StatusSubscriptionEnum;
use App\Domains\Billing\Events\SubscriptionActivated;
use App\Models\billing\Subscription;

class SubscriptionStateService
{
    private const TRANSITIONS = [
        StatusSubscriptionEnum::PENDING   => [StatusSubscriptionEnum::ACTIVE, StatusSubscriptionEnum::CANCELED],
        StatusSubscriptionEnum::ACTIVE    => [StatusSubscriptionEnum::CANCELED, StatusSubscriptionEnum::SUSPENDED, StatusSubscriptionEnum::EXPIRED],
        StatusSubscriptionEnum::SUSPENDED => [StatusSubscriptionEnum::ACTIVE, StatusSubscriptionEnum::EXPIRED],
        StatusSubscriptionEnum::EXPIRED   => [StatusSubscriptionEnum::ACTIVE],
        StatusSubscriptionEnum::CANCELED  => [],
    ];

    public function transition(
        Subscription $subscription,
        StatusSubscriptionEnum $to
    ): Subscription {
        $this->guardTransition($subscription->status, $to);

        $subscription->update(['status' => $to]);

        if ($to === StatusSubscriptionEnum::ACTIVE) {
            event(new SubscriptionActivated($subscription));
        }

        return $subscription;
    }

    private function guardTransition(StatusSubscriptionEnum $from, StatusSubscriptionEnum $to): void
    {
        $allowed = self::TRANSITIONS[$from] ?? [];

        if (! in_array($to, $allowed, true)) {
            abort(422, "Cannot transition subscription from [{$from->value}] to [{$to->value}]");
        }
    }
}
