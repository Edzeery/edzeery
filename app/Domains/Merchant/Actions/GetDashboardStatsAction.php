<?php

namespace App\Domains\Merchant\Actions;

use App\Domains\Merchant\Services\DashboardMetricsService;
use App\Models\User;

class GetDashboardStatsAction
{
    public function __construct(
        protected DashboardMetricsService $metricsService
    ) {}

    public function execute(User $user): array
    {
        return collect(
            $this->metricsService->build($user)
        )
            ->map(fn ($metric) => $metric->toArray())
            ->toArray();
    }
}
