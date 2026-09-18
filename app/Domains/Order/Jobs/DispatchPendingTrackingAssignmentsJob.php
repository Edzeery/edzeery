<?php

namespace App\Domains\Order\Jobs;

use App\Domains\Order\Services\OrderTrackingAssignmentService;
use App\Enums\Store\OrderTrackingStatus;
use App\Models\Orders\OrderTracking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DispatchPendingTrackingAssignmentsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    public function handle(OrderTrackingAssignmentService $assignmentService): void
    {
        $unassigned = OrderTracking::whereNull('assigned_to_membership_id')
            ->whereNull('assignment_method')
            ->where('tracking_status', OrderTrackingStatus::SHIPPED->value)
            ->with('store')
            ->get();

        foreach ($unassigned as $tracking) {
            try {
                $assignmentService->assign($tracking);
            } catch (\Exception $e) {
                Log::error('Failed to auto-assign order tracking', [
                    'tracking_id' => $tracking->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}