<?php

namespace App\Domains\Billing\Actions;

use App\Domains\Billing\Enums\ManualPaymentMethodEnum;
use App\Domains\Billing\Events\PaymentSubmitted;
use App\Enums\SubscriptionPayment\StatusPaymentEnum;
use App\Models\billing\Payment;
use App\Models\billing\Subscription;
use Illuminate\Http\UploadedFile;

class SubmitManualPaymentAction
{
    public function execute(
        Subscription $subscription,
        ManualPaymentMethodEnum $method,
        string $referenceNumber,
        ?UploadedFile $proofFile = null,
    ): Payment {

        $proofPath = null;
        if ($proofFile) {
            $proofPath = $proofFile->store('billing/proofs', 'public');
        }

        $payment = Payment::create([
            'user_id'         => $subscription->user_id,
            'store_id'        => currentStoreId() ?? $subscription->user->stores()->first()?->id,
            'subscription_id' => $subscription->id,
            'plan_price_id'   => $subscription->plan_price_id,
            'gateway'         => 'manual',
            'transaction_id'  => null,
            'status'          => StatusPaymentEnum::PENDING_REVIEW,
            'amount'          => $subscription->planPrice?->price ?? 0,
            'currency'        => 'DZD',
            'paid_at'         => null,
            'manual_method'   => $method->value,
            'reference_number' => $referenceNumber,
            'proof_file_path' => $proofPath,
        ]);

        event(new PaymentSubmitted($payment));

        return $payment;
    }
}
