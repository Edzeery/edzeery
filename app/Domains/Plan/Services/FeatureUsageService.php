<?php

namespace App\Domains\Plan\Services;

use App\Models\billing\Subscription;
use App\Models\Plans\PlanFeature;
use Illuminate\Support\Facades\DB;

class FeatureUsageService
{
    /* ================= CHECK ================= */

    public function canUse(Subscription $subscription, string $featureSlug, int $amount = 1): bool
    {
        $feature = $this->getFeature($subscription, $featureSlug);

        if (!$feature) {
            return false;
        }

        $value = $subscription->plan->getFeatureValue($featureSlug);

        // unlimited
        if ($value === 'unlimited' || $value === null) {
            return true;
        }

        // boolean feature
        if ($feature->type === 'boolean') {
            return (bool) $value;
        }

        // quota check
        if ($feature->quota) {
            $used = $this->getConsumption($subscription, $feature->id);
            return ($used + $amount) <= (int) $value;
        }

        return true;
    }

    /* ================= CONSUME ================= */

    public function consume(Subscription $subscription, string $featureSlug, int $amount = 1): void
    {
        $feature = $this->getFeature($subscription, $featureSlug);

        if (!$feature || !$feature->consumable) {
            return;
        }

        DB::transaction(function () use ($subscription, $feature, $amount) {

            $record = $subscription->featureConsumptions()
                ->firstOrCreate([
                    'plan_feature_id' => $feature->id,
                ], [
                    'consumption' => 0,
                ]);

            $record->increment('consumption', $amount);
        });
    }

    /* ================= GET ================= */

    public function getConsumption(Subscription $subscription, string $featureId): int
    {
        return (int) $subscription->featureConsumptions()
            ->where('plan_feature_id', $featureId)
            ->value('consumption') ?? 0;
    }

    /* ================= DECREMENT ================= */

    public function decrement(Subscription $subscription, string $featureSlug, int $amount = 1): void
    {
        $feature = $this->getFeature($subscription, $featureSlug);

        if (!$feature || !$feature->consumable) {
            return;
        }

        DB::transaction(function () use ($subscription, $feature, $amount) {
            $record = $subscription->featureConsumptions()
                ->where('plan_feature_id', $feature->id)
                ->first();

            if ($record && $record->consumption > 0) {
                $record->decrement('consumption', min($amount, $record->consumption));
            }
        });
    }

    /* ================= RESET ================= */

    public function reset(Subscription $subscription, PlanFeature $feature): void
    {
        $subscription->featureConsumptions()
            ->where('plan_feature_id', $feature->id)
            ->update([
                'consumption' => 0,
                'expired_at' => now(),
            ]);
    }

    /* ================= INTERNAL ================= */

    protected function getFeature(Subscription $subscription, string $slug): ?PlanFeature
    {
        return $subscription->plan->features
            ->firstWhere('slug', $slug);
    }
}
