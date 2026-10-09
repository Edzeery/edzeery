<?php

namespace App\Domains\Order\Jobs;

use App\Domains\Order\Services\OrderAssignmentService;
use App\Domains\Order\Services\OrderTrackingAssignmentService;
use App\Models\Stores\Store;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ShiftHandoverJob implements ShouldBeUniqueUntilProcessing, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Store $store
    ) {}

    public int $tries = 3;

    public int $timeout = 120;

    /**
     * The sweep is idempotent per store, so a burst of dispatches (shift
     * saves, team edits) must collapse into one queued pass. The unique lock
     * is held until the job finishes; if a later dispatch arrives meanwhile
     * it is dropped rather than queued behind a duplicate sweep.
     */
    public int $uniqueFor = 600;

    public function uniqueId(): string
    {
        return "shift-handover:{$this->store->id}";
    }

    public function handle(OrderAssignmentService $assignmentService, OrderTrackingAssignmentService $trackingService): void
    {
        $assignmentService->handleShiftHandover($this->store);
        $trackingService->handleShiftHandover($this->store);
    }
}
