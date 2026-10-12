@php
    $periods = [
        'all' => __('dashboard.period_all'),
        'today' => __('dashboard.period_today'),
        'yesterday' => __('dashboard.period_yesterday'),
        'week' => __('dashboard.period_week'),
        'month' => __('dashboard.period_month'),
        'custom' => __('dashboard.period_custom'),
    ];

    // The stats view is chosen by permission, not by whether a member is selected.
    $canConfirmStats = $canConfirm || $canStatsConfirmation;
    $canSwitchStatsView = $canConfirmStats && $canStatsDelivery;
    $defaultDimension = $canConfirmStats ? 'confirmation' : 'delivery';

    $activeCarrier = $filter->carrierId
        ? $filterOptions['carriers']->firstWhere('id', $filter->carrierId)
        : null;

    // The sentinel is not a membership, so it never matches a real row.
    $unattributedSelected = $filter->memberId === \App\Domains\Analytics\DTOs\DashboardFilter::UNATTRIBUTED;

    $activeMember = ! $unattributedSelected && $filter->memberId
        ? $filterOptions['members']->firstWhere('id', $filter->memberId)
        : null;

    $dimensionLabel = $filter->memberDimension
        ? __("dashboard.dimension_{$filter->memberDimension}")
        : null;

    // Only worth a chip once it differs from what this user would get anyway.
    $dimensionIsOverridden = $filter->memberDimension !== null
        && $filter->memberDimension !== $defaultDimension;

    // The carrier filter belongs to the delivery view only (D4); the date basis
    // and the product filter are confirmation-view refinements.
    $dateBasisDiffers = ! $isDeliveryView
        && $filter->dateBasis !== \App\Domains\Analytics\DTOs\DashboardFilter::DATE_BASIS_CREATED;
    $productsSelected = ! $isDeliveryView && count($filter->productIds ?? []) > 0;

    $hasActiveFilters = $filter->period !== 'today'
        || filled($activeCarrier)
        || filled($activeMember)
        || $unattributedSelected
        || $dimensionIsOverridden
        || $dateBasisDiffers
        || $productsSelected;
@endphp

<div class="mb-6 flex flex-col gap-3">
    <div class="flex flex-wrap items-center gap-2">
        @foreach ($periods as $key => $label)
            <button type="button"
                wire:click="setPeriod('{{ $key }}')"
                @disabled($filter->period === $key)
                aria-pressed="{{ $filter->period === $key ? 'true' : 'false' }}"
                @class([
                    'px-3 py-1.5 text-xs sm:text-sm rounded-full border transition-colors',
                    'bg-brand-600 text-white border-brand-600' => $filter->period === $key,
                    'border-surface-border bg-surface text-ink hover:bg-surface-secondary' => $filter->period !== $key,
                ])>
                {{ $label }}
            </button>
        @endforeach

        {{-- T5: the store link lives here as a header action (copy link + a
             compact visit button) for both stats views, instead of a card of
             its own under the filters. --}}
        @php($storeUrl = currentStore()?->public_url)
        <div class="ms-auto flex items-center gap-1.5" x-data="{ copied: false }">
            <span class="hidden max-w-[14rem] truncate font-mono text-xs text-ink-muted lg:inline">{{ $storeUrl ?? '-' }}</span>
            <button type="button"
                x-on:click="navigator.clipboard.writeText('{{ $storeUrl }}'); copied = true; setTimeout(() => copied = false, 2000);"
                class="edz-btn edz-btn--ghost edz-btn--sm"
                title="{{ __('buttons.copy_link') }}"
                aria-label="{{ __('buttons.copy_link') }}">
                <x-edz.icon name="copy" class="h-4 w-4" />
                <span class="sr-only" x-text="copied ? '{{ __('buttons.copied') }}' : '{{ __('buttons.copy_link') }}'"></span>
            </button>
            @if (currentStore()?->isPubliclyActive())
                <a href="{{ $storeUrl }}" target="_blank" rel="noopener noreferrer"
                   class="edz-btn edz-btn--secondary edz-btn--sm">
                    <x-edz.icon name="external-link" class="h-4 w-4 me-1" />
                    {{ __('storefront.visit_store') }}
                </a>
            @endif
        </div>
    </div>

    @if ($filter->period === 'custom')
        <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center">
            <div class="flex flex-col gap-1 w-full sm:w-auto">
                <label class="edz-label" for="dashboard-date-from">{{ __('dashboard.date_from') }}</label>
                <input id="dashboard-date-from" type="text" x-data="{}"
                       x-init="flatpickr($el,{dateFormat:'Y-m-d'})"
                       wire:model.blur="dateFrom"
                       class="edz-input text-sm @if ($invalidRange) edz-input--error @endif"
                       placeholder="YYYY-MM-DD">
            </div>
            <div class="flex flex-col gap-1 w-full sm:w-auto">
                <label class="edz-label" for="dashboard-date-to">{{ __('dashboard.date_to') }}</label>
                <input id="dashboard-date-to" type="text" x-data="{}"
                       x-init="flatpickr($el,{dateFormat:'Y-m-d'})"
                       wire:model.blur="dateTo"
                       class="edz-input text-sm @if ($invalidRange) edz-input--error @endif"
                       placeholder="YYYY-MM-DD">
            </div>
            @if ($invalidRange)
                <p class="w-full sm:w-auto sm:pe-1 text-xs text-warning-fg-strong">
                    {{ __('dashboard.invalid_range') }}
                </p>
            @endif
        </div>
    @endif

    <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center">
        @if ($isDeliveryView)
            <div class="flex flex-col gap-1 w-full sm:w-auto min-w-[15rem] md:min-w-[14rem]">
                <label class="edz-label" for="dashboard-carrier">{{ __('dashboard.filter_carrier') }}</label>
                <x-edz.select
                    wire:model.live="carrierId"
                    :options="$filterOptions['carrierOptions']"
                    search
                    :placeholder="__('dashboard.all_carriers')"
                    :searchPlaceholder="__('dashboard.search_carrier')"
                />
                @if ($activeCarrier && ! $activeCarrier->is_active)
                    <p class="text-xs text-warning-fg-strong">{{ __('dashboard.carrier_inactive') }}</p>
                @endif
            </div>
        @endif

        {{-- Product filter: confirmation view only. --}}
        @if (! $isDeliveryView)
            <div class="flex flex-col gap-1 w-full sm:w-auto min-w-[15rem] md:min-w-[14rem]">
                <label class="edz-label" for="dashboard-product-filter">{{ __('dashboard.filter_products') }}</label>
                <button type="button" id="dashboard-product-filter" wire:click="openProductFilter"
                    class="edz-input text-sm flex items-center justify-between gap-2 text-start">
                    <span @class(['truncate', 'text-ink-muted' => count($productIds) === 0])>
                        {{ count($productIds) > 0
                            ? count($productIds).' '.__('dashboard.products_selected')
                            : __('dashboard.products_selected') }}
                    </span>
                    <x-edz.icon name="chevron-down" class="w-4 h-4 text-ink-muted shrink-0" />
                </button>
            </div>
        @endif

        @unless ($filter->memberLocked)
            @if ($filterOptions['memberOptions'])
                <div class="flex flex-col gap-1 w-full sm:w-auto min-w-[15rem] md:min-w-[14rem]">
                    <label class="edz-label" for="dashboard-member">{{ __('dashboard.filter_member') }}</label>
                    <x-edz.select
                        wire:model.live="memberId"
                        :options="$filterOptions['memberOptions']"
                        search
                        :placeholder="__('dashboard.all_members')"
                        :searchPlaceholder="__('dashboard.search_member')"
                    />
                </div>
            @endif
        @endunless

        {{-- Date basis: confirmation view only (orders.created_at vs last status change). --}}
        @if (! $isDeliveryView)
            <div class="flex flex-col gap-1 w-full sm:w-auto">
                <span class="edz-label">{{ __('dashboard.date_basis') }}</span>
                <div class="flex items-center gap-3 px-3 py-2 rounded-md border border-surface-border bg-surface">
                    @foreach (['created' => 'date_basis_created', 'last_status_changed' => 'date_basis_last_status'] as $value => $label)
                        <label class="flex items-center gap-1.5 text-xs text-ink" for="dashboard-basis-{{ $value }}">
                            <input id="dashboard-basis-{{ $value }}"
                                   type="radio"
                                   value="{{ $value }}"
                                   wire:model.live="dateBasis"
                                   class="rounded">
                            <span>{{ __("dashboard.{$label}") }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Anyone who can read both sides of the dashboard switches the whole
             view; anyone who can read only one gets a fixed badge. --}}
        @if ($canSwitchStatsView)
            <div class="flex flex-col gap-1 w-full sm:w-auto">
                <span class="edz-label">{{ __('dashboard.stats_view') }}</span>
                <div class="flex items-center gap-3 px-3 py-2 rounded-md border border-surface-border bg-surface">
                    @foreach (['confirmation', 'delivery'] as $dimension)
                        <label class="flex items-center gap-1.5 text-xs text-ink" for="dashboard-dimension-{{ $dimension }}">
                            <input id="dashboard-dimension-{{ $dimension }}"
                                   type="radio"
                                   value="{{ $dimension }}"
                                   wire:model.live="memberDimension"
                                   @disabled($dimension === ($filter->memberDimension ?? $defaultDimension))
                                   class="rounded">
                            <span>{{ __("dashboard.dimension_{$dimension}") }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @elseif (filled($dimensionLabel))
            <div class="flex flex-col gap-1 w-full sm:w-auto">
                <span class="edz-label">{{ __('dashboard.stats_view') }}</span>
                <span class="edz-badge edz-badge--neutral">{{ $dimensionLabel }}</span>
            </div>
        @endif
    </div>

    @if ($hasActiveFilters)
        <div class="flex flex-wrap items-center gap-2 border-t border-surface-border pt-3">
            <span class="text-xs font-medium text-ink-muted">{{ __('dashboard.active_filters') }}</span>

            @if ($filter->period !== 'today')
                <span class="edz-badge edz-badge--neutral">{{ $periodLabel }}</span>
            @endif

            @if ($activeCarrier)
                <span class="edz-badge edz-badge--neutral">
                    {{ $activeCarrier->name }}
                    @unless ($activeCarrier->is_active)
                        ({{ __('dashboard.carrier_inactive') }})
                    @endunless
                </span>
            @endif

            @if ($activeMember)
                <span class="edz-badge edz-badge--neutral">{{ $activeMember->name }}</span>
            @endif

            @if ($unattributedSelected)
                <span class="edz-badge edz-badge--neutral">
                    {{ $isDeliveryView ? __('dashboard.team_unattributed') : __('dashboard.team_unassigned') }}
                </span>
            @endif

            @if ($dimensionIsOverridden)
                <span class="edz-badge edz-badge--neutral">{{ $dimensionLabel }}</span>
            @endif

            @if ($productsSelected)
                <span class="edz-badge edz-badge--neutral">{{ count($filter->productIds) }} {{ __('dashboard.products_selected') }}</span>
            @endif

            @if ($dateBasisDiffers)
                <span class="edz-badge edz-badge--neutral">{{ __('dashboard.date_basis_last_status') }}</span>
            @endif

            <button type="button" wire:click="resetFilters" class="edz-btn edz-btn--secondary edz-btn--sm ms-auto">
                {{ __('dashboard.reset_filters') }}
            </button>
        </div>
    @endif
</div>

@if (! $isDeliveryView)
    @include('livewire.merchant.dashboard.partials.product-filter-modal')
@endif
