<?php

namespace App\Livewire\Concerns;

use App\Domains\Analytics\DTOs\DashboardFilter;
use App\Domains\Analytics\Support\DashboardFilterFactory;
use Livewire\Attributes\Url;

trait DashboardFilterConcern
{
    #[Url(as: 'p')]
    public string $period = 'today';

    #[Url(as: 'df')]
    public ?string $dateFrom = null;

    #[Url(as: 'dt')]
    public ?string $dateTo = null;

    #[Url(as: 'c')]
    public ?string $carrierId = null;

    /**
     * The confirmation team section's without/with-trash toggle (D7). It only
     * decides whether soft-deleted members are named in the team section; it
     * never turns off the deleted-orders bracket, which is always shown.
     */
    #[Url(as: 'tt')]
    public bool $teamTrash = false;

    #[Url(as: 'm')]
    public ?string $memberId = null;

    #[Url(as: 'md')]
    public ?string $memberDimension = null;

    #[Url(as: 'db')]
    public string $dateBasis = DashboardFilter::DATE_BASIS_CREATED;

    /** @var array<int, string> */
    #[Url(as: 'pr')]
    public array $productIds = [];

    /** @var array<int, string> */
    #[Url(as: 'vr')]
    public array $variantIds = [];

    #[Url(as: 'ct')]
    public string $confirmationTab = 'count';

    /**
     * Free-text search over the confirmation team cards (member name). It is a
     * client-side refinement of the already-scanned rows, so it is deliberately
     * kept off the URL like the rest of the transient view state.
     */
    public string $teamSearch = '';

    /**
     * Period pills go through here instead of a raw "$set" so that a request
     * for the period that is already active is a no-op: re-clicking the active
     * pill used to re-render the dashboard with an identical filter, whose
     * in-place morph stripped the width/height/style attributes Chart.js had
     * written onto its canvases and left the charts blank until a reload.
     * The active pill is also rendered disabled, so this guard mainly catches
     * a same-value request that reaches the server another way (e.g. the
     * period is in the URL and the history entry is re-entered).
     */
    public function setPeriod(string $period): void
    {
        if ($period === $this->period) {
            return;
        }

        $this->period = $period;
    }

    public function filter(): DashboardFilter
    {
        $current = auth()->user()?->storeMemberships()->where('store_id', currentStoreId())->first();
        $factory = app(DashboardFilterFactory::class);

        return $factory->make([
            'period' => $this->period,
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
            'carrierId' => $this->carrierId,
            'memberId' => $this->memberId,
            'memberDimension' => $this->memberDimension,
            'dateBasis' => $this->dateBasis,
            'productIds' => $this->productIds,
            'variantIds' => $this->variantIds,
        ], $current);
    }

    public function setConfirmationTab(string $tab): void
    {
        if (! in_array($tab, ['count', 'rate'], true) || $tab === $this->confirmationTab) {
            return;
        }

        $this->confirmationTab = $tab;
    }

    public function setTeamTrash(bool $value): void
    {
        if ($value === $this->teamTrash) {
            return;
        }

        $this->teamTrash = $value;
    }

    public function resetFilters(): void
    {
        $this->period = 'today';
        $this->dateFrom = null;
        $this->dateTo = null;
        $this->carrierId = null;
        $this->memberId = null;
        $this->memberDimension = null;
        $this->dateBasis = DashboardFilter::DATE_BASIS_CREATED;
        $this->productIds = [];
        $this->variantIds = [];
        $this->confirmationTab = 'count';
        $this->teamTrash = false;
        $this->teamSearch = '';
        $this->dispatch('dashboard-filters-reset');
    }
}
