<?php

namespace App\Domains\Billing\Actions;

use App\Domains\Billing\DTOs\SubscriptionData;
use App\Models\billing\Subscription;
use App\Models\Plans\Plan;
use Illuminate\Support\Facades\DB;

class ChangePlanAction
{
    public function execute(Subscription $subscription, Plan $newPlan): Subscription
    {
        return DB::transaction(function () use ($subscription, $newPlan) {

            // // 1. حدد مدة الاشتراك الحالي والفترة المتبقية
            // $remainingDays = $subscription->ends_at?->diffInDays(now()) ?? 0;

            // // 2. قم بتحديث الخطة
            // $subscription->update([
            //     'plan_id' => $newPlan->id,
            //     'plan_price_id' => $newPlan->prices()->firstWhere('billing_period', 'monthly')->id,
            //     'was_switched' => true,
            // ]);

            // // 3. تحديث تاريخ الانتهاء إذا أردت احتساب الرصيد
            // if ($remainingDays > 0) {
            //     $subscription->ends_at = now()->addDays($newPlan->trial_days + $remainingDays);
            //     $subscription->save();
            // }

            // return $subscription;
            return app(CreateSubscriptionAction::class)->execute(
                new SubscriptionData(
                    user: $subscription->user,
                    plan: $newPlan,
                    planPrice: $newPlan->prices()->first(),
                    isTrial: false
                )
            );
        });
    }
}
