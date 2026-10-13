<?php

namespace App\Domains\Analytics\Services;

use App\Domains\Analytics\DTOs\DashboardFilter;
use App\Domains\Analytics\Support\ConfirmationRate;
use App\Domains\Analytics\Support\ConfirmationStatsQuery;
use App\Domains\Analytics\Support\ConfirmationStatsReport;

/**
 * PHASE 37-L — the single confirmation read behind the headline cards, the
 * rate doughnut and the team section. One grouped read per filter hash
 * (memoised), folded by ConfirmationStatsReport so every confirmation figure
 * on a render comes from the same scan and can never disagree.
 */
class StoreConfirmationReportReader
{
    /**
     * @var array<string, list<array{membership_id: ?string, status_key: string, total: int, deleted: int}>>
     */
    private array $rows = [];

    /**
     * @return array{groups: array<string, array{total: int, deleted: int}>, confirmed: int, denominator: int, rate: int, kind: string, excluded: int, outside: array<string, bool>}
     */
    public function report(DashboardFilter $filter): array
    {
        return $this->reportFor($filter)->build($this->rows($filter));
    }

    /**
     * @return array<string, array{groups: array<string, array{total: int, deleted: int}>, confirmed: int, denominator: int, rate: int, kind: string, excluded: int, outside: array<string, bool>}>
     */
    public function perMember(DashboardFilter $filter): array
    {
        return $this->reportFor($filter)->perMember($this->rows($filter));
    }

    /**
     * @return list<array{membership_id: ?string, status_key: string, total: int, deleted: int}>
     */
    private function rows(DashboardFilter $filter): array
    {
        return $this->rows[$filter->hash()] ??= app(ConfirmationStatsQuery::class)->run($filter);
    }

    /**
     * The report's rules (PHASE 37-N2): the date basis picks gross or net, and
     * the store's locked exclusions drop their status keys from every term.
     * currentStore() was already resolved (settings included) earlier in the
     * request, so this is a cached relation — no extra query on render.
     */
    private function reportFor(DashboardFilter $filter): ConfirmationStatsReport
    {
        return new ConfirmationStatsReport(
            ConfirmationRate::kindForBasis($filter->dateBasis),
            currentStore()?->settings?->excludedConfirmationStatusKeys() ?? [],
        );
    }
}
