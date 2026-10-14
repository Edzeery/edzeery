<?php

namespace App\Livewire\Concerns;

use App\Domains\Analytics\DTOs\DashboardFilter;
use App\Domains\Analytics\Support\DashboardFilterFactory;
use App\Domains\Analytics\Support\DashboardMemberCohort;
use App\Enums\Store\StorePermissionEnum;
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

    /**
     * The legacy view seed (pre-37-N5 links): `?md=delivery` used to select the
     * whole dashboard view. It is kept only so old links keep working; the
     * authoritative view state is `$tab`, which seeds from this when absent.
     */
    #[Url(as: 'md')]
    public ?string $memberDimension = null;

    /**
     * The active dashboard view (PHASE 37-N5): 'confirmation', 'delivery' or
     * 'top'. It is validated against the tabs this user may open, so a
     * hand-edited URL can never open a view the user cannot read.
     */
    #[Url(as: 't')]
    public ?string $tab = null;

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
     * The delivery body's count/rate switch (PHASE 37-N8), mirroring the
     * confirmation tab. The rate tab holds the per-status breakdown; the count
     * tab holds the headline totals. It never touches the KPI row above it.
     */
    #[Url(as: 'dv')]
    public string $deliveryTab = 'count';

    /**
     * Free-text search over the confirmation team cards (member name). It is a
     * client-side refinement of the already-scanned rows, so it is deliberately
     * kept off the URL like the rest of the transient view state.
     */
    public string $teamSearch = '';

    /**
     * The Delivery team section's cohort pick (PHASE 37-N7, D8): all members,
     * only the confirmation team, or only the rest. Off the URL, like the
     * search, because it narrows an already-scanned list.
     */
    public string $teamCohort = 'all';

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
            'memberDimension' => $this->activeDimension(),
            'dateBasis' => $this->dateBasis,
            'productIds' => $this->productIds,
            'variantIds' => $this->variantIds,
            'view' => $this->activeTab(),
        ], $current);
    }

    /**
     * The tabs this user may open, in display order. Confirmation is the desk's
     * own numbers (a confirmer or a confirmation-stats reader); delivery is the
     * shipped-orders reader; top is the executive board, gated by the same
     * STATS_TOP_KPIS permission the old KPI row used.
     *
     * @return array<int, string>
     */
    public function permittedTabs(): array
    {
        $tabs = [];

        if (canStore(StorePermissionEnum::ORDER_CONFIRM->value)
            || canStore(StorePermissionEnum::STATS_CONFIRMATION->value)) {
            $tabs[] = 'confirmation';
        }

        if (canStore(StorePermissionEnum::STATS_DELIVERY->value)) {
            $tabs[] = 'delivery';
        }

        if (canStore(StorePermissionEnum::STATS_TOP_KPIS->value)) {
            $tabs[] = 'top';
        }

        return $tabs;
    }

    /**
     * The tab actually shown: the URL's `t` when it is one the user may open,
     * otherwise the legacy `md` seed, otherwise the first permitted tab. Never
     * returns a tab the user cannot see.
     */
    public function activeTab(): string
    {
        $permitted = $this->permittedTabs();

        if ($this->tab !== null && in_array($this->tab, $permitted, true)) {
            return $this->tab;
        }

        if ($this->memberDimension !== null && in_array($this->memberDimension, $permitted, true)) {
            return $this->memberDimension;
        }

        return $permitted[0] ?? 'confirmation';
    }

    /**
     * The member-attribution dimension the active tab reads on. Top is a
     * store-wide executive view with no member filter, so it answers on the
     * confirmation key while the factory drops every filter that has no control.
     */
    public function activeDimension(): string
    {
        return $this->activeTab() === 'delivery' ? 'delivery' : 'confirmation';
    }

    /**
     * Switch the whole dashboard view. A tab the user may not open, or the tab
     * already showing, is a no-op (so an unprivileged crafted request has no
     * effect). The legacy `md` seed and the member pick belong to the old view
     * and are cleared with it.
     */
    public function setTab(string $tab): void
    {
        if (! in_array($tab, $this->permittedTabs(), true) || $tab === $this->activeTab()) {
            return;
        }

        $this->tab = $tab;
        $this->memberDimension = null;
        $this->memberId = null;
        $this->teamSearch = '';
    }

    public function setConfirmationTab(string $tab): void
    {
        if (! in_array($tab, ['count', 'rate'], true) || $tab === $this->confirmationTab) {
            return;
        }

        $this->confirmationTab = $tab;
    }

    public function setDeliveryTab(string $tab): void
    {
        if (! in_array($tab, ['count', 'rate'], true) || $tab === $this->deliveryTab) {
            return;
        }

        $this->deliveryTab = $tab;
    }

    public function setTeamTrash(bool $value): void
    {
        if ($value === $this->teamTrash) {
            return;
        }

        $this->teamTrash = $value;
    }

    /**
     * PHASE 37-N7 (D8): the Delivery team section's cohort pick. An unknown
     * value is ignored, and re-picking the active one is a no-op.
     */
    public function setTeamCohort(string $cohort): void
    {
        if (! in_array($cohort, [DashboardMemberCohort::COHORT_ALL, DashboardMemberCohort::COHORT_WITH, DashboardMemberCohort::COHORT_WITHOUT], true)) {
            return;
        }

        if ($cohort === $this->teamCohort) {
            return;
        }

        $this->teamCohort = $cohort;
    }

    public function resetFilters(): void
    {
        $this->period = 'today';
        $this->dateFrom = null;
        $this->dateTo = null;
        $this->carrierId = null;
        $this->memberId = null;
        $this->memberDimension = null;
        $this->tab = null;
        $this->dateBasis = DashboardFilter::DATE_BASIS_CREATED;
        $this->productIds = [];
        $this->variantIds = [];
        $this->confirmationTab = 'count';
        $this->deliveryTab = 'count';
        $this->teamTrash = false;
        $this->teamSearch = '';
        $this->teamCohort = DashboardMemberCohort::COHORT_ALL;
        $this->dispatch('dashboard-filters-reset');
    }
}
