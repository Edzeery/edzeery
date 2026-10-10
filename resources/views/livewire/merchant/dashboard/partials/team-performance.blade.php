{{-- Team performance (37-J): one aggregate query feeds every row, and the columns
     follow the active stats view so the table speaks the same language as the KPIs. --}}
<div wire:key="dash-team-{{ $filter->hash() }}" class="edz-card edz-card--padded mb-6">
    <div class="flex items-start justify-between gap-4 mb-4">
        <div>
            <h3 class="text-sm font-semibold tracking-tight text-ink">{{ __('dashboard.team_performance_title') }}</h3>
            <p class="mt-1 text-xs text-ink-muted">{{ $isDeliveryView
                ? __('dashboard.team_performance_delivery')
                : __('dashboard.team_performance_confirmation') }}</p>
        </div>
        <span class="shrink-0 text-xs text-ink-muted">{{ $periodLabel }}</span>
    </div>

    @php
        $countColumns = $isDeliveryView
            ? [
                ['label' => 'dashboard.col_assigned', 'key' => 'assigned'],
                ['label' => 'dashboard.col_delivered', 'key' => 'delivered'],
                ['label' => 'dashboard.col_returned', 'key' => 'returned'],
                ['label' => 'dashboard.col_in_progress', 'key' => 'in_progress'],
            ]
            : [
                // Workload ("المُسند") first, then credit: what was handed to the
                // member, then what the member's confirmed base turned into.
                ['label' => 'dashboard.col_assigned', 'key' => 'assigned'],
                ['label' => 'dashboard.col_pending', 'key' => 'pending'],
                ['label' => 'dashboard.col_canceled', 'key' => 'canceled'],
                ['label' => 'dashboard.col_other', 'key' => 'other'],
                ['label' => 'dashboard.col_confirmed', 'key' => 'confirmed'],
                ['label' => 'dashboard.col_delivered', 'key' => 'delivered'],
                ['label' => 'dashboard.col_returned', 'key' => 'returned'],
            ];

        // Bar colours reuse the KPI thresholds: high confirmation and delivery
        // are good, a high return rate is not.
        $rateColumns = $isDeliveryView
            ? [
                ['label' => 'dashboard.col_delivery_rate', 'key' => 'delivery_rate', 'min' => 70, 'inverse' => false],
                ['label' => 'dashboard.col_return_rate', 'key' => 'return_rate', 'min' => 10, 'inverse' => true],
            ]
            : [
                ['label' => 'dashboard.col_confirmation_rate', 'key' => 'confirmation_rate', 'min' => 70, 'inverse' => false],
            ];

        $hasActivity = array_sum(array_column($teamPerformance, 'assigned')) > 0;
    @endphp

    @unless ($hasActivity)
        <div class="py-10 text-center">
            <x-edz.icon name="user" class="w-10 h-10 mx-auto text-ink-muted mb-2" />
            <p class="text-sm text-ink-muted">{{ __('dashboard.team_no_activity') }}</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full min-w-[44rem] text-sm">
                <thead>
                    <tr class="border-b border-surface-border text-start text-xs uppercase tracking-wider text-ink-muted">
                        <th class="sticky start-0 z-10 bg-surface px-4 py-3 text-start font-semibold">{{ __('dashboard.col_member') }}</th>
                        @foreach ($countColumns as $column)
                            <th class="px-4 py-3 text-end font-semibold">{{ __($column['label']) }}</th>
                        @endforeach
                        @foreach ($rateColumns as $column)
                            <th class="px-4 py-3 text-end font-semibold">{{ __($column['label']) }}</th>
                        @endforeach
                        @if ($isDeliveryView)
                            <th class="px-4 py-3 text-end font-semibold">{{ __('dashboard.col_delivered_revenue') }}</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach ($teamPerformance as $row)
                        <tr @class([
                            'border-b border-surface-border last:border-0',
                            'hover:bg-surface-secondary/50' => $row['type'] === 'member',
                            'bg-surface-secondary font-bold' => $row['type'] === 'total',
                            'text-ink-muted italic' => $row['type'] === 'unattributed',
                        ])>
                            <td class="sticky start-0 z-10 px-4 py-3 whitespace-nowrap {{ $row['type'] === 'total' ? 'bg-surface-secondary' : 'bg-surface' }}">{{ $row['name'] }}</td>
                            @foreach ($countColumns as $column)
                                <td class="px-4 py-3 text-end tabular-nums">{{ number_format($row[$column['key']]) }}</td>
                            @endforeach
                            @foreach ($rateColumns as $column)
                                @php
                                    $value = $row[$column['key']];
                                    $onTrack = $column['inverse'] ? $value <= $column['min'] : $value >= $column['min'];
                                @endphp
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <span class="hidden w-16 overflow-hidden rounded-full bg-surface-secondary h-1.5 sm:block">
                                            <span class="block rounded-full h-1.5 transition-all duration-700 ease-out-expo {{ $onTrack
                                                ? 'bg-success-500'
                                                : ($column['inverse'] ? 'bg-danger-500' : 'bg-warning-500') }}"
                                                style="width: {{ $value }}%"></span>
                                        </span>
                                        <span class="text-xs font-semibold tabular-nums">{{ $value }}%</span>
                                    </div>
                                </td>
                            @endforeach
                            @if ($isDeliveryView)
                                <td class="px-4 py-3 text-end font-semibold tabular-nums">{{ currency($row['revenue']) }}</td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endunless
</div>
