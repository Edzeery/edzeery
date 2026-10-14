{{-- Delivery (tracking) team section — PHASE 37-N7 (D8/D9): the Delivery tab's
     team cards. A centred search and a cohort pick (all / confirmation team /
     without confirmation) plus the members-only trash toggle refine the already
     scanned rows; the member dropdown keeps the full dimension list. Cards read
     as vertical panels and close on the delivery-rate bar, mirroring the
     confirmation team section. --}}
@php
    $deliveryMetrics = [
        ['label' => 'dashboard.col_assigned', 'key' => 'assigned'],
        ['label' => 'dashboard.col_delivered', 'key' => 'delivered'],
        ['label' => 'dashboard.col_returned', 'key' => 'returned'],
        ['label' => 'dashboard.col_in_progress', 'key' => 'in_progress'],
    ];

    // Bar colours reuse the KPI thresholds: high delivery is good, a high
    // return rate is not.
    $deliveryRates = [
        ['label' => 'dashboard.col_delivery_rate', 'key' => 'delivery_rate', 'min' => 70, 'inverse' => false],
        ['label' => 'dashboard.col_return_rate', 'key' => 'return_rate', 'min' => 10, 'inverse' => true],
    ];

    // The trailing total row is answered by the member cards (and the headline
    // cards above), so it is dropped here instead of being rendered as its own.
    $allRows = collect($teamPerformance ?? [])
        ->reject(fn (array $row) => ($row['type'] ?? null) === 'total')
        ->values();

    $typeLabels = [
        'unattributed' => __('dashboard.team_unattributed'),
        'total' => __('dashboard.team_total'),
    ];

    // The search is a client-side refinement of the already-scanned rows: it
    // narrows the named members only.
    $search = trim(mb_strtolower((string) ($teamSearch ?? '')));
    $rows = $search === ''
        ? $allRows
        : $allRows->filter(function (array $row) use ($search, $typeLabels) {
            $name = (string) ($row['name'] ?? ($typeLabels[$row['type']] ?? $row['type']));

            return str_contains(mb_strtolower($name), $search);
        })->values();

    $hasActivity = $allRows->sum(fn (array $row) => (int) $row['assigned'] + (int) $row['delivered'] + (int) $row['returned']) > 0;
    $noResults = $search !== '' && $rows->isEmpty();

    // D8: the cohort pick. Labels follow the confirmation team vocabulary.
    $cohorts = [
        'all' => 'dashboard.team_cohort_all',
        'with' => 'dashboard.team_cohort_with',
        'without' => 'dashboard.team_cohort_without',
    ];

    $cohortOptions = collect($cohorts)
        ->map(fn (string $label, string $value) => ['value' => $value, 'label' => __($label)])
        ->values()
        ->all();
@endphp

<div wire:key="dash-team-{{ $filter->hash() }}-{{ $teamCohort }}-{{ $teamTrash ? 'trash' : 'clean' }}"
     class="edz-card edz-card--padded mb-6">
    <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-center">
        <div class="min-w-0 lg:flex-1">
            <h3 class="text-base font-semibold tracking-tight text-ink sm:text-lg">{{ __('dashboard.team_performance_title') }}</h3>
            <p class="mt-1 text-sm text-ink-muted">{{ __('dashboard.team_performance_delivery') }}</p>
        </div>

        {{-- Search sits between the title and the controls. --}}
        <div class="flex justify-center lg:flex-1">
            <div class="relative w-full max-w-xs">
                <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3 text-ink-muted">
                    <x-edz.icon name="magnifying-glass" class="h-4 w-4" />
                </span>
                <input type="search"
                       wire:model.live.debounce.300ms="teamSearch"
                       placeholder="{{ __('dashboard.search_member') }}"
                       aria-label="{{ __('dashboard.search_member') }}"
                       class="edz-input w-full ps-9 text-sm">
            </div>
        </div>

        <div class="flex flex-col items-center gap-2 lg:flex-1 lg:items-end">
            <div class="flex flex-wrap items-center justify-center gap-2 lg:justify-end">
                {{-- Cohort pick (D8): narrows the named members to the
                     confirmation team, or to everyone else. --}}
                <label for="dash-team-cohort" class="sr-only">{{ __('dashboard.team_cohort_label') }}</label>
                <x-edz.select
                    id="dash-team-cohort"
                    wire:model="teamCohort"
                    wire:change="setTeamCohort($event.target.value)"
                    :options="$cohortOptions"
                    size="sm"
                    :placeholder="__('dashboard.team_cohort_label')"
                    class="min-w-[11rem]" />

                {{-- Members-only trash toggle: never turns off the order badge. --}}
                <div class="flex items-center gap-1 rounded-lg bg-surface-secondary/70 p-1" role="group">
                    @foreach (['clean' => false, 'with' => true] as $key => $value)
                        <button type="button"
                            wire:click="setTeamTrash({{ $value ? 'true' : 'false' }})"
                            aria-pressed="{{ $teamTrash === $value ? 'true' : 'false' }}"
                            @class([
                                'rounded-md px-3 py-1.5 text-sm font-medium transition-colors',
                                'bg-surface text-ink shadow-sm' => $teamTrash === $value,
                                'text-ink-muted hover:text-ink' => $teamTrash !== $value,
                            ])>
                            {{ __("dashboard.team_members_{$key}_trash") }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    @if (! $hasActivity)
        <div class="flex flex-col items-center justify-center py-8 text-center">
            <x-edz.icon name="truck" class="mb-2 h-10 w-10 text-ink-muted" />
            <p class="text-sm text-ink-muted">{{ __('dashboard.team_no_activity') }}</p>
        </div>
    @elseif ($noResults)
        <div class="flex flex-col items-center justify-center py-8 text-center">
            <x-edz.icon name="magnifying-glass" class="mb-2 h-10 w-10 text-ink-muted" />
            <p class="text-sm text-ink-muted">{{ __('dashboard.no_data') }}</p>
        </div>
    @else
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($rows as $row)
                @php
                    $rowName = $row['name'] ?? ($typeLabels[$row['type']] ?? $row['type']);
                @endphp
                <div wire:key="dash-team-card-{{ $row['membership_id'] ?? $row['type'] }}" @class([
                    'flex flex-col gap-4 rounded-xl border p-4',
                    'border-surface-border bg-surface' => $row['type'] === 'member',
                    'border-transparent bg-surface-secondary' => $row['type'] === 'total',
                    'border-dashed border-surface-border bg-surface/60 text-ink-muted' => $row['type'] === 'unattributed',
                ])>
                    {{-- Status: the member the card speaks for. --}}
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-3">
                            @if ($row['type'] === 'member')
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-accent-500/10 text-sm font-bold text-accent-600">
                                    {{ mb_strtoupper(mb_substr($rowName, 0, 1)) }}
                                </span>
                            @else
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-surface-secondary text-ink-muted">
                                    <x-edz.icon name="user" class="h-4 w-4" />
                                </span>
                            @endif
                            <div class="min-w-0 text-start">
                                <p @class([
                                    'truncate text-sm',
                                    'font-semibold text-ink' => $row['type'] !== 'unattributed',
                                    'italic font-medium text-ink-muted' => $row['type'] === 'unattributed',
                                ])>{{ $rowName }}</p>
                                <p class="mt-0.5 text-xs text-ink-muted">
                                    {{ __('dashboard.col_delivered_revenue') }}:
                                    <span class="font-semibold tabular-nums">{{ currency($row['revenue']) }}</span>
                                </p>
                            </div>
                        </div>
                        @if ($row['type'] === 'total')
                            <span class="shrink-0 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">{{ __('dashboard.team_total') }}</span>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        @foreach ($deliveryMetrics as $metric)
                            @php
                                $deleted = (int) ($row[$metric['key'].'_deleted'] ?? 0);
                            @endphp
                            <div class="rounded-lg bg-surface-secondary/60 p-2.5">
                                <p class="truncate text-[10px] font-medium uppercase tracking-wider text-ink-muted">{{ __($metric['label']) }}</p>
                                <div class="mt-1 flex items-center gap-1.5">
                                    <p class="text-xl font-bold leading-none tracking-tighter text-ink tabular-nums">{{ $row[$metric['key']] }}</p>
                                    @if ($deleted > 0)
                                        <x-edz.tooltip :label="__('dashboard.deleted_brackets', ['count' => $deleted])" side="top"
                                            class="inline-flex items-center gap-0.5 text-[11px] font-semibold leading-none text-danger-fg-strong tabular-nums">
                                            <x-edz.icon name="trash" class="w-3 h-3" />
                                            <span>{{ $deleted }}</span>
                                        </x-edz.tooltip>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- The two return states ride under the metrics only when the
                         member actually carries them. --}}
                    @if (($row['returned_coming'] ?? 0) > 0 || ($row['returned_processed'] ?? 0) > 0)
                        <div class="flex items-center gap-4 rounded-lg bg-warning-500/5 px-3 py-2 text-xs">
                            <span class="flex items-center gap-1.5 text-ink-muted">
                                <span class="h-2 w-2 rounded-full bg-warning-500"></span>
                                {{ __('dashboard.returned_coming') }}
                                <span class="font-semibold text-ink tabular-nums">{{ $row['returned_coming'] ?? 0 }}</span>
                            </span>
                            <span class="flex items-center gap-1.5 text-ink-muted">
                                <span class="h-2 w-2 rounded-full bg-success-500"></span>
                                {{ __('dashboard.returned_processed') }}
                                <span class="font-semibold text-ink tabular-nums">{{ $row['returned_processed'] ?? 0 }}</span>
                            </span>
                        </div>
                    @endif

                    <div class="mt-auto space-y-2">
                        @foreach ($deliveryRates as $column)
                            @php
                                $value = $row[$column['key']];
                                $healthy = $column['inverse'] ? $value <= $column['min'] : $value >= $column['min'];
                            @endphp
                            <div class="flex items-center gap-2">
                                <span class="w-24 truncate text-[10px] font-medium uppercase tracking-wider text-ink-muted">{{ __($column['label']) }}</span>
                                <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-surface-secondary">
                                    <div @class([
                                        'h-1.5 rounded-full transition-all duration-700 ease-out-expo',
                                        'bg-success-500' => $healthy,
                                        'bg-danger-500' => ! $healthy && $column['inverse'],
                                        'bg-warning-500' => ! $healthy && ! $column['inverse'],
                                    ]) style="width: {{ $value }}%"></div>
                                </div>
                                <span class="w-9 text-right text-xs font-semibold tabular-nums">{{ $value }}%</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
