<?php

use App\Models\Plans\PlanFeature;
use App\Services\Subscriptions\FeatureUsageService;

if (! function_exists('canUseFeature')) {

    function canUseFeature(
        string $featureSlug,
        ?int $storeId = null
    ): bool {

        $subscription = user()?->latestSubscription();


        if (
            ! $subscription ||
            ! $subscription->isActive()
        ) {
            return false;
        }

        $feature = PlanFeature::where('slug', $featureSlug)->first();


        if (! $feature) {
            return false;
        }

        $pivot = $subscription
            ->plan
            ->features
            ->firstWhere('id', $feature->id)
            ?->pivot;

        if (! $pivot) {
            return false;
        }

        $value = $pivot->value;

        // unlimited
        if ($value === 'unlimited') {
            return true;
        }

        // boolean
        if ($feature->type === 'boolean') {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }

        // number
        if ($feature->type === 'number') {
            $usage = app(FeatureUsageService::class)
                ->getConsumption($subscription, $feature->id);

            return $usage < (int) $value;
        }

        return false;
    }
}
