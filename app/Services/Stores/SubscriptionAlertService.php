<?php

namespace App\Services\Stores;

use App\Models\Stores\Store;
use Filament\Notifications\Notification;
use Carbon\Carbon;

class SubscriptionAlertService
{
    /**
     * تحقق من الاشتراك وأرسل إشعارات Filament لكل المتاجر.
     */
    public static function check(Store $store): array
    {
        $issues = [];
        $subscription = $store->subscription;

        if (!$subscription) {
            $issues[] = [
                'store' => $store,
                'message' => 'No subscription yet.',
            ];
        } elseif ($subscription->is_trial && now()->gt($subscription->trial_ends_at)) {
            $issues[] = [
                'store' => $store,
                'message' => 'Trial ended.',
            ];
        } elseif ($subscription->ends_at && now()->gt($subscription->ends_at)) {
            $issues[] = [
                'store' => $store,
                'message' => 'Subscription expired.',
            ];
        }

        // إرسال إشعارات Filament
        foreach ($issues as $issue) {
            Notification::make()
                ->title("Store: {$issue['store']->name}")
                ->body($issue['message'])
                ->danger()
                ->send();
        }

        

        return $issues;
    }

    /**
     * تحقق لجميع متاجر المستخدم
     */
    public static function checkAllUserStores($user): array
    {
        $allIssues = [];
        foreach ($user->stores as $store) {
            $storeIssues = self::check($store);
            $allIssues = array_merge($allIssues, $storeIssues);
        }
        return $allIssues;
    }
}
