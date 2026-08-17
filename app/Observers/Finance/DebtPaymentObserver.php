<?php

namespace App\Observers\Finance;

use App\Domains\Finance\Services\DebtSettlementService;
use App\Enums\Finance\DebtStatusEnum;
use App\Models\Finance\DebtPayment;

class DebtPaymentObserver
{
    public function __construct(
        private DebtSettlementService $settlementService,
    ) {}

    public function created(DebtPayment $payment): void
    {
        $this->syncDebtStatus($payment);
    }

    public function updated(DebtPayment $payment): void
    {
        $this->syncDebtStatus($payment);
    }

    public function deleted(DebtPayment $payment): void
    {
        $this->syncDebtStatus($payment);
    }

    private function syncDebtStatus(DebtPayment $payment): void
    {
        $debt = $payment->debt;
        if (! $debt) {
            return;
        }

        $totalPaid = $debt->payments()->sum('amount');
        $status = match (true) {
            $totalPaid >= $debt->total_amount => DebtStatusEnum::PAID,
            $totalPaid > 0 => DebtStatusEnum::PARTIAL,
            default => DebtStatusEnum::ACTIVE,
        };

        $debt->update([
            'paid_amount' => $totalPaid,
            'status' => $status,
        ]);
    }
}
