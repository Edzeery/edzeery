<?php

namespace App\Domains\Finance\Services;

use App\Models\Finance\Debt;
use Carbon\Carbon;

class DebtNotificationService
{
    /**
     * Get debts that are due within the given number of days.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Debt>
     */
    public function getUpcomingDueDebts(int $days = 7): \Illuminate\Database\Eloquent\Collection
    {
        return Debt::query()
            ->active()
            ->whereNotNull('due_date')
            ->where('due_date', '<=', Carbon::now()->addDays($days))
            ->where('due_date', '>=', Carbon::now())
            ->get();
    }

    /**
     * Get debts that are overdue.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Debt>
     */
    public function getOverdueDebts(): \Illuminate\Database\Eloquent\Collection
    {
        return Debt::query()
            ->overdue()
            ->get();
    }

    /**
     * Format a reminder message for a debt.
     */
    public function reminderMessage(Debt $debt): string
    {
        $remaining = $this->remainingAmount($debt);
        $dueDate = $debt->due_date->format('Y-m-d');

        return __('finance.debt_reminder_message', [
            'name' => $debt->counterparty_name ?? '—',
            'amount' => number_format($remaining, 2),
            'date' => $dueDate,
        ]);
    }

    /**
     * Calculate remaining amount for a debt.
     */
    public function remainingAmount(Debt $debt): float
    {
        return max(0, $debt->total_amount - $debt->paid_amount);
    }
}
