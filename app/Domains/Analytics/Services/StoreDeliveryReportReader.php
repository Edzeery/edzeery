<?php

namespace App\Domains\Analytics\Services;

use App\Domains\Analytics\DTOs\DashboardFilter;
use App\Domains\Analytics\Support\DeliveryRate;
use App\Domains\Analytics\Support\DeliveryStatsQuery;
use App\Domains\Analytics\Support\DeliveryStatsReport;

/**
 * PHASE 37-N7 (D10) — the single delivery read behind the rate cards and the
 * headline KPIs. One grouped read per filter hash (memoised), folded by
 * {@see DeliveryStatsReport} so every delivery figure on a render comes from
 * the same scan and can never disagree.
 */
class StoreDeliveryReportReader
{
    /**
     * @var array<string, list<array{status_key: string, total: int, deleted: int}>>
     */
    private array $rows = [];

    /**
     * @return array{kind: string, delivered: int, returned: int, denominator: int, delivery_rate: int, return_rate: int, excluded: int, deleted: int, groups: array<string, array{total: int, deleted: int}>, outside: array<string, bool>, total_orders: int, trash_orders: int}
     */
    public function report(DashboardFilter $filter): array
    {
        return $this->reportFor($filter)->build($this->rows($filter));
    }

    /**
     * @return list<array{status_key: string, total: int, deleted: int}>
     */
    private function rows(DashboardFilter $filter): array
    {
        return $this->rows[$filter->hash()] ??= app(DeliveryStatsQuery::class)->run($filter);
    }

    /**
     * The report's rules (D3/D4): the date basis picks gross or net, and the
     * store's locked exclusions drop their status keys from every term.
     * currentStore() was already resolved (settings included) earlier in the
     * request, so this is a cached relation — no extra query on render.
     */
    private function reportFor(DashboardFilter $filter): DeliveryStatsReport
    {
        return new DeliveryStatsReport(
            DeliveryRate::kindForBasis($filter->dateBasis),
            currentStore()?->settings?->excludedDeliveryStatusKeys() ?? [],
        );
    }
}
