<?php

namespace App\Domains\Analytics\Services;

use App\Domains\Analytics\DTOs\DashboardFilter;
use App\Domains\Analytics\Support\ConfirmationRateDenominator;
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
     * @return array{groups: array<string, array{total: int, deleted: int}>, confirmed: int, denominator: int, rate: int}
     */
    public function report(DashboardFilter $filter): array
    {
        return (new ConfirmationStatsReport($this->denominatorGroups()))
            ->build($this->rows($filter));
    }

    /**
     * @return array<string, array{groups: array<string, array{total: int, deleted: int}>, confirmed: int, denominator: int, rate: int}>
     */
    public function perMember(DashboardFilter $filter): array
    {
        return (new ConfirmationStatsReport($this->denominatorGroups()))
            ->perMember($this->rows($filter));
    }

    /**
     * @return list<array{membership_id: ?string, status_key: string, total: int, deleted: int}>
     */
    private function rows(DashboardFilter $filter): array
    {
        return $this->rows[$filter->hash()] ??= app(ConfirmationStatsQuery::class)->run($filter);
    }

    /**
     * The denominator preset from store_settings.confirmation_rate_denominator.
     * currentStore() was already resolved (settings included) earlier in the
     * request, so this is a cached relation — no extra query on render.
     *
     * @return list<string>
     */
    private function denominatorGroups(): array
    {
        return ConfirmationRateDenominator::resolve(
            currentStore()?->settings?->confirmation_rate_denominator
        );
    }
}
