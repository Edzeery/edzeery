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

    $activeMember = $filter->memberId
        ? $filterOptions['members']->firstWhere('id', $filter->memberId)
        : null;

    $dimensionLabel = $filter->memberDimension
        ? __("dashboard.dimension_{$filter->memberDimension}")
        : null;

    // Only worth a chip once it differs from what this user would get anyway.
    $dimensionIsOverridden = $filter->memberDimension !== null
        && $filter->memberDimension !== $defaultDimension;

    $hasActiveFilters = $filter->period !== 'today'
        || filled($activeCarrier)
        || filled($activeMember)
        || $dimensionIsOverridden;
@endphp

<div class="mb-6 flex flex-col gap-3">
    <div class="flex flex-wrap items-center gap-2">
        @foreach ($periods as $key => $label)
            <button type="button"
                wire:click="$set('period','{{ $key }}')"
                @class([
                    'px-3 py-1.5 text-xs sm:text-sm rounded-full border transition-colors',
                    'bg-brand-600 text-white border-brand-600' => $filter->period === $key,
                    'border-surface-border bg-surface text-ink hover:bg-surface-secondary' => $filter->period !== $key,
                ])>
                {{ $label }}
            </button>
        @endforeach
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
        <div class="flex flex-col gap-1 w-full sm:w-auto">
            <label class="edz-label" for="dashboard-carrier">{{ __('dashboard.filter_carrier') }}</label>
            <select id="dashboard-carrier" wire:model="carrierId" class="edz-input text-sm">
                <option value="">{{ __('dashboard.all_carriers') }}</option>
                @foreach ($filterOptions['carriers'] as $carrier)
                    <option value="{{ $carrier->id }}">{{ $carrier->name }}</option>
                @endforeach
            </select>
            @if ($activeCarrier && ! $activeCarrier->is_active)
                <p class="text-xs text-warning-fg-strong">{{ __('dashboard.carrier_inactive') }}</p>
            @endif
        </div>

        @unless ($filter->memberLocked)
            @if ($filterOptions['members']->isNotEmpty())
                <div class="flex flex-col gap-1 w-full sm:w-auto">
                    <label class="edz-label" for="dashboard-member">{{ __('dashboard.filter_member') }}</label>
                    <select id="dashboard-member" wire:model="memberId" class="edz-input text-sm">
                        <option value="">{{ __('dashboard.all_members') }}</option>
                        @foreach ($filterOptions['members'] as $member)
                            <option value="{{ $member->id }}">{{ $member->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        @endunless

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

            @if ($dimensionIsOverridden)
                <span class="edz-badge edz-badge--neutral">{{ $dimensionLabel }}</span>
            @endif

            <button type="button" wire:click="resetFilters" class="edz-btn edz-btn--secondary edz-btn--sm ms-auto">
                {{ __('dashboard.reset_filters') }}
            </button>
        </div>
    @endif
</div>
