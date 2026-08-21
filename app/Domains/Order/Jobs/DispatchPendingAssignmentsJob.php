<?php

namespace App\Domains\Order\Jobs;

use App\Domains\Order\Services\OrderAssignmentService;
use App\Models\Orders\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DispatchPendingAssignmentsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    public function handle(OrderAssignmentService $assignmentService): void
    {
        $unassigned = Order::whereNull('assigned_to_membership_id')
            ->whereNull('assignment_method')
            ->whereHas('status', fn ($q) => $q->where('key', 'pending'))
            ->with('store')
            ->get();

        foreach ($unassigned as $order) {
            try {
                $assignmentService->assign($order);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to auto-assign order', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
