<?php

namespace App\Domains\Billing\Listeners;


class HandleSubscriptionCreated
{
    public function handle($event): void
    {
        $subscription = $event->subscription;

        // ممكن تجهيز trial tracking
        // أو إرسال إشعار ترحيبي
    }
}
