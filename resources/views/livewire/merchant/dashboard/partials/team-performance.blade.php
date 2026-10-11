{{-- Team performance (37-J): one aggregate query feeds every row. This partial
     is the delivery view only and reads as vertical cards; the confirmation view
     renders its own tab-following section (dashboard/partials/confirmation-team). --}}
<div wire:key="dash-team-{{ $filter->hash() }}" class="edz-card edz-card--padded mb-6">
    <div class="flex items-start justify-between gap-4 mb-4">
        <div>
            <h3 class="text-sm font-semibold tracking-tight text-ink">{{ __('dashboard.team_performance_title') }}</h3>
            <p class="mt-1 text-xs text-ink-muted">{{ __('dashboard.team_performance_delivery') }}</p>
        </div>
        <span class="shrink-0 text-xs text-ink-muted">{{ $periodLabel }}</span>
    </div>

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

        $hasActivity = array_sum(array_column($teamPerformance, 'assigned')) > 0;
    @endphp

    @unless ($hasActivity)
        <div class="py-10 text-center">
            <x-edz.icon name="user" class="w-10 h-10 mx-auto text-ink-muted mb-2" />
            <p class="text-sm text-ink-muted">{{ __('dashboard.team_no_activity') }}</p>
        </div>
    @else
        {{-- Vertical cards. Every metric keeps a trashed sibling as a trash badge
             so a deleted order still reads against the type it was assigned,
             delivered or returned as. --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($teamPerformance as $row)
                    <div @class([
                        'flex flex-col gap-4 rounded-xl border p-4',
                        'border-surface-border bg-surface' => $row['type'] === 'member',
                        'border-transparent bg-surface-secondary' => $row['type'] === 'total',
                        'border-dashed border-surface-border bg-surface/60 text-ink-muted' => $row['type'] === 'unattributed',
                    ])>
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p @class([
                                    'truncate text-sm',
                                    'font-semibold text-ink' => $row['type'] !== 'unattributed',
                                    'italic' => $row['type'] === 'unattributed',
                                ])>{{ $row['name'] }}</p>
                                <p class="mt-0.5 text-xs text-ink-muted">
                                    {{ __('dashboard.col_delivered_revenue') }}:
                                    <span class="font-semibold tabular-nums">{{ currency($row['revenue']) }}</span>
                                </p>
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

                        <div class="space-y-2">
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
    @endunless
</div>
