<?php

namespace App\Domains\Billing\Actions;

use App\Domains\Billing\Events\PaymentRejected;
use App\Domains\Billing\Events\PaymentSucceeded;
use App\Enums\SubscriptionPayment\StatusPaymentEnum;
use App\Models\billing\Payment;
use App\Models\User;

class ReviewManualPaymentAction
{
    public function approve(Payment $payment, User $reviewer): Payment
    {
        abort_if($payment->status !== StatusPaymentEnum::PENDING_REVIEW, 422, 'Payment is not pending review.');

        $payment->update([
            'status'      => StatusPaymentEnum::PAID,
            'paid_at'     => now(),
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
        ]);

        event(new PaymentSucceeded($payment));

        return $payment->refresh();
    }

    public function reject(Payment $payment, User $reviewer, string $reason): Payment
    {
        abort_if($payment->status !== StatusPaymentEnum::PENDING_REVIEW, 422, 'Payment is not pending review.');

        $payment->update([
            'status'           => StatusPaymentEnum::CANCELED,
            'reviewed_by'      => $reviewer->id,
            'reviewed_at'      => now(),
            'rejection_reason' => $reason,
        ]);

        event(new PaymentRejected($payment));

        return $payment->refresh();
    }
}
