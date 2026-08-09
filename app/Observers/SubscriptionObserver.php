<?php

namespace App\Observers;

use App\Models\billing\Subscription;
use App\Services\Stores\StoreStatusUpdater;

class SubscriptionObserver
{
    public function updated(Subscription $subscription)
    {
        // تحديث حالة المتجر عند أي تغيير في ends_at أو is_trial أو status
        if ($subscription->wasChanged(['ends_at', 'is_trial', 'status'])) {
            StoreStatusUpdater::update($subscription->user, 'Subscription updated by observer');
        }
    }

    public function created(Subscription $subscription)
    {
        // عند إنشاء اشتراك جديد
        StoreStatusUpdater::update($subscription->user, 'New subscription created');
    }
}
