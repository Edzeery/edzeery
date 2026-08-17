<?php

namespace App\Domains\Finance\Services;

use App\Models\Finance\Debt;

class DebtSettlementService
{
    /**
     * Check if the debt is fully settled.
     */
    public function isSettled(Debt $debt): bool
    {
        return $debt->paid_amount >= $debt->total_amount;
    }

    /**
     * Calculate remaining amount for a debt.
     */
    public function remainingAmount(Debt $debt): float
    {
        return max(0, $debt->total_amount - $debt->paid_amount);
    }

    /**
     * Calculate settlement progress percentage.
     */
    public function progress(Debt $debt): float
    {
        if ($debt->total_amount <= 0) {
            return 0;
        }

        return round(($debt->paid_amount / $debt->total_amount) * 100, 2);
    }
}
