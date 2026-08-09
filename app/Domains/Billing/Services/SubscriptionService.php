<?php

namespace App\Domains\Billing\Services;

use App\Domains\Billing\Actions\ActivateSubscriptionAction;
use App\Domains\Billing\Actions\CreateSubscriptionAction;
use App\Domains\Billing\Actions\RecordPaymentAction;
use App\Domains\Billing\DTOs\SubscriptionData;
use App\Models\billing\Subscription;

class SubscriptionService
{
    public function __construct(
        protected CreateSubscriptionAction $createAction,
        protected RecordPaymentAction $paymentAction,
        protected ActivateSubscriptionAction $activateAction,
    ) {}

    public function createWithPayment(
        SubscriptionData $data,
        string $gateway = 'system',
        string $transactionId = null
    ): Subscription {

        $subscription = $this->createAction->execute($data);

        $this->paymentAction->execute(
            subscription: $subscription,
            gateway: $gateway,
            transactionId: $transactionId ?? uniqid(),
            amount: $data->planPrice->price,
            currency: $data->plan->currency ?? 'DZD'
        );

        // ❌ لا تفعل هنا
        // activation يتم فقط لما payment = paid

        return $subscription->refresh();
    }
}
