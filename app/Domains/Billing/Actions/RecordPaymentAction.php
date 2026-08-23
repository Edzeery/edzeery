<?php

namespace App\Domains\Billing\Actions;

use App\Domains\Billing\Events\PaymentSucceeded;
use App\Enums\SubscriptionPayment\StatusPaymentEnum;
use App\Models\billing\Payment;
use App\Models\billing\Subscription;

class RecordPaymentAction
{
    public function execute(
        Subscription $subscription,
        string $gateway,
        ?string $transactionId = null,
        float $amount = 0,
        string $currency = 'DZD',
        StatusPaymentEnum $status = StatusPaymentEnum::PAID,
        ?array $meta = null,
    ): Payment {

        $payment = Payment::create([
            'user_id'         => $subscription->user_id,
            'store_id'        => currentStoreId() ?? $subscription->user->stores()->first()?->id,
            'subscription_id' => $subscription->id,
            'plan_price_id'   => $subscription->plan_price_id,
            'gateway'         => $gateway,
            'transaction_id'  => $transactionId ?? ('txn_' . uniqid()),
            'status'          => $status,
            'amount'          => $amount,
            'currency'        => $currency,
            'meta'            => $meta,
            'paid_at'         => $status === StatusPaymentEnum::PAID ? now() : null,
        ]);

        if ($payment->isPaid()) {
            event(new PaymentSucceeded($payment));
        }

        return $payment;
    }
}
