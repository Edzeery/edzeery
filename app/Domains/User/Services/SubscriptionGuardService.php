<?php

namespace App\Domains\User\Services;

use App\Models\billing\Subscription;
use App\Models\User;

class SubscriptionGuardService
{
    public function hasActiveSubscription(?User $user = null): bool
    {
        $user = $user ?? user();

        if (! $user) {
            return false;
        }

        $subscription = $user->latestSubscription();

        return $subscription && ($subscription->isActive() || $subscription->onTrial());
    }

    public function getSubscription(?User $user = null): ?Subscription
    {
        $user = $user ?? user();

        return $user?->latestSubscription();
    }

    public function isTrial(?User $user = null): bool
    {
        $subscription = $this->getSubscription($user);

        return $subscription && $subscription->onTrial();
    }

    public function isExpired(?User $user = null): bool
    {
        $subscription = $this->getSubscription($user);

        if (! $subscription) {
            return true;
        }

        return ! $subscription->isActive() && ! $subscription->onTrial();
    }

    public function daysRemaining(?User $user = null): ?int
    {
        $subscription = $this->getSubscription($user);

        if (! $subscription || ! $subscription->ends_at) {
            return null;
        }

        $days = (int) now()->diffInDays($subscription->ends_at, false);

        return max(0, $days);
    }

    public function statusLabel(?User $user = null): string
    {
        if ($this->isTrial($user)) {
            return 'trial';
        }

        if ($this->hasActiveSubscription($user)) {
            return 'active';
        }

        return 'expired';
    }
}
