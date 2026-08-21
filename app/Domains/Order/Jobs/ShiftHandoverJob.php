<?php

namespace App\Domains\Order\Jobs;

use App\Domains\Order\Services\OrderAssignmentService;
use App\Models\Stores\Store;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ShiftHandoverJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Store $store
    ) {}

    public int $tries = 3;
    public int $timeout = 120;

    public function handle(OrderAssignmentService $assignmentService): void
    {
        $assignmentService->handleShiftHandover($this->store);
    }
}
