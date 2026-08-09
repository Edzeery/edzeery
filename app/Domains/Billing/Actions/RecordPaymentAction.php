<?php

namespace App\Domains\Billing\Actions;

use App\Enums\SubscriptionPayment\StatusPaymentEnum;
use App\Models\billing\Payment;
use App\Models\billing\Subscription;

class RecordPaymentAction
{
    public function execute(
        Subscription $subscription,
        string $gateway,
        string $transactionId,
        float $amount,
        string $currency = 'DZD',
        StatusPaymentEnum $status = StatusPaymentEnum::PAID
    ): Payment {


        $payment = Payment::create([
            'user_id'         => $subscription->user_id,
            'subscription_id' => $subscription->id,
            'plan_price_id'   => $subscription->plan_price_id,
            'gateway'         => $gateway,
            'transaction_id'  => $transactionId,
            'status'          => $status,
            'amount'          => $amount,
            'currency'        => $currency,
            'paid_at'         => $status === StatusPaymentEnum::PAID ? now() : null,
        ]);
        if ($payment->isPaid()) {
            app(ActivateSubscriptionAction::class)
                ->execute($subscription);
        }
        return $payment;
    }
}


// public function execute(array $payload): Payment
// {
//     return DB::transaction(function () use ($payload) {
//         $payment = Payment::updateOrCreate(
//             ['transaction_id' => $payload['transaction_id']],
//             [
//                 'user_id' => $payload['user_id'],
//                 'store_id' => $payload['store_id'],
//                 'subscription_id' => $payload['subscription_id'],
//                 'plan_id' => $payload['plan_id'],
//                 'plan_price_id' => $payload['plan_price_id'],
//                 'gateway' => $payload['gateway'],
//                 'status' => $payload['status'],
//                 'amount' => $payload['amount'],
//                 'currency' => $payload['currency'],
//                 'paid_at' => $payload['paid_at'] ?? now(),
//                 'meta' => $payload['meta'] ?? [],
//             ]
//         );

//         // إذا الدفع مكتمل، فعل الاشتراك
//         if ($payment->isPaid()) {
//             (new ActivateSubscriptionAction())->execute($payment->subscription);
//         }

//         return $payment;
//     });
// }
