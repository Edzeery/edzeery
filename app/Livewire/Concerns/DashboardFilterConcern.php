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

    #[Url(as: 'm')]
    public ?string $memberId = null;

    #[Url(as: 'md')]
    public ?string $memberDimension = null;

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
        ], $current);
    }

    public function resetFilters(): void
    {
        $this->period = 'today';
        $this->dateFrom = null;
        $this->dateTo = null;
        $this->carrierId = null;
        $this->memberId = null;
        $this->memberDimension = null;
        $this->dispatch('dashboard-filters-reset');
    }
}
