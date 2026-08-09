<?php

namespace App\Domains\Merchant\Services;

use App\Domains\Merchant\DTOs\DashboardStatData;
use App\Models\Stores\Team\StoreMembership;
use App\Models\User;
use Illuminate\Support\Collection;

class DashboardMetricsService
{
    public function build(User $user): array
    {
        $stores = $user->stores()->get();

        $totalStores = $stores->count();

        $activeStores = $stores->where('status', 'active')->count();
        $pendingStores = $stores->where('status', 'pending')->count();

        $uniqueMembersCount = $this->getUniqueMembersCount($stores->pluck('id'));

        // $current = $this->getUniqueMembersCount($stores->pluck('id'));
        // $lastMonth = $this->getUniqueMembersCountLastMonth($stores->pluck('id'));

        // $growth = $lastMonth > 0
        //     ? round((($current - $lastMonth) / $lastMonth) * 100, 1)
        //     : 100;
        return [
            new DashboardStatData(

                icon: getIconHtml('store','ion','w-6 h-6'),
                title: __('titles.stores_count'),
                count: $totalStores,
                desc: "{$activeStores} active • {$pendingStores} pending",
                percentageResult: $totalStores > 0
                    ? round(($activeStores / $totalStores) * 100, 1)
                    : 0,
                trend: 'up',
            ),

            new DashboardStatData(
                title: __('titles.memberships'),
                count: $uniqueMembersCount,
                desc: __('dashboard.total_memberships'),
            ),
        ];
    }
    protected function getUniqueMembersCount(Collection $storeIds): int
    {
        return StoreMembership::query()
            ->whereIn('store_id', $storeIds)
            ->where('user_id', '!=', user()->id)
            ->where('is_active', true)
            ->distinct()
            ->count('user_id');
    }
}
