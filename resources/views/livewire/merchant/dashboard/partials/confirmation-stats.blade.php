@php
    // M4 confirmation tab — the body of the global count / rate mode.
    // Every number comes from $confirmationStats (ConfirmationStatsQuery +
    // ConfirmationStatsReport), so the headline, the group cards and the rate
    // always answer the same scan.

    $rateGroups = ['confirmed', 'canceled', 'pending', 'failed', 'duplicate', 'deferred'];

    $groupDot = [
        'confirmed' => 'bg-success-500',
        'pending' => 'bg-warning-500',
        'failed' => 'bg-danger-500',
        'canceled' => 'bg-ink-muted',
        'deferred' => 'bg-accent-400',
        'duplicate' => 'bg-indigo-400',
        'delivery_outcomes' => 'bg-sky-400',
        'unclassified' => 'bg-ink-muted/50',
    ];

    $groups = $confirmationStats['groups'] ?? [];
    $confirmed = (int) ($confirmationStats['confirmed'] ?? 0);
    $denominator = (int) ($confirmationStats['denominator'] ?? 0);
    $rate = (int) ($confirmationStats['rate'] ?? 0);
    $totalOrders = collect($groups)->sum('total');
    $trashOrders = collect($groups)->sum('deleted');
@endphp

<div wire:key="confirmation-stats-{{ $filter->hash() }}"
     class="edz-card edz-card--padded h-full">
    <h3 class="mb-5 text-base font-semibold tracking-tight text-ink sm:text-lg">{{ __('dashboard.confirmation_stats_title') }}</h3>

    @if ($confirmationTab === 'count')
        {{-- Count tab: the desk's 30-second read — all orders and trash stacked
             as two large centred figures. The per-status breakdown lives on the
             rate tab, so this tab never grows a wall of group cards. --}}
        <div class="grid grid-cols-1 gap-4">
            <div class="rounded-2xl bg-surface-secondary/60 p-6 text-center">
                <span class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-surface text-accent-600 shadow-sm">
                    <x-edz.icon name="cart" class="h-6 w-6" />
                </span>
                <p class="text-sm font-medium uppercase tracking-wider text-gray-600 dark:text-ink-muted">{{ __('dashboard.total_orders') }}</p>
                <p class="mt-2 text-5xl font-bold leading-none tracking-tighter text-ink">{{ $totalOrders }}</p>
                <p class="mt-2 text-sm text-gray-600 dark:text-ink-muted">{{ __('dashboard.confirmed_of_total', ['total' => $totalOrders]) }}, {{ __('dashboard.group_confirmed') }}: {{ $confirmed }}</p>
            </div>
            <div class="rounded-2xl bg-surface-secondary/60 p-6 text-center">
                <span class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-danger-surface text-danger-fg-strong shadow-sm">
                    <x-edz.icon name="trash" class="h-6 w-6" />
                </span>
                <p class="text-sm font-medium uppercase tracking-wider text-gray-600 dark:text-ink-muted">{{ __('dashboard.trash_orders') }}</p>
                <p @class([
                    'mt-2 text-5xl font-bold leading-none tracking-tighter',
                    'text-danger-600' => $trashOrders > 0,
                    'text-gray-500 dark:text-ink-muted' => $trashOrders === 0,
                ])>{{ $trashOrders }}</p>
                <p class="mt-2 text-sm text-gray-600 dark:text-ink-muted">{{ __('dashboard.deleted_brackets', ['count' => $trashOrders]) }}</p>
            </div>
        </div>
    @else
        {{-- Rate tab: the rate and its horizontal group row read full width,
             and the charts with the doughnut drop underneath. --}}
        <div class="flex items-end justify-between gap-3">
            <div class="min-w-0">
                <p class="text-sm font-medium uppercase tracking-wider text-gray-600 dark:text-ink-muted">{{ __('dashboard.confirmation_rate') }}</p>
                <div class="mt-1 flex items-center gap-2">
                    <p class="text-5xl font-bold tracking-tighter text-ink leading-none">{{ $rate }}<span class="text-2xl font-semibold text-gray-600 dark:text-ink-muted">%</span></p>
                    <x-edz.tooltip :label="__('dashboard.confirmation_rate_explainer', ['confirmed' => $confirmed, 'denominator' => $denominator, 'rate' => $rate])" side="top" maxWidth="360px"
                        class="text-gray-400 transition-colors hover:text-gray-600 dark:text-ink-muted/70 dark:hover:text-ink-muted">
                        <x-edz.icon name="information-circle" class="w-5 h-5" />
                    </x-edz.tooltip>
                </div>
                <p class="mt-2 text-sm text-gray-600 dark:text-ink-muted">{{ __('dashboard.confirmation_rate_basis', ['confirmed' => $confirmed, 'denominator' => $denominator]) }}</p>
            </div>
            <span @class([
                'inline-flex items-center gap-0.5 text-sm font-semibold px-2 py-1 rounded-full shrink-0',
                'text-success-fg-strong bg-success-surface' => $rate >= 70,
                'text-warning-fg-strong bg-warning-surface' => $rate < 70,
            ])>
                {{ $rate }}%
            </span>
        </div>
        <div class="w-full bg-surface-secondary rounded-full h-2.5 overflow-hidden my-5">
            <div class="h-2.5 rounded-full transition-all duration-700 ease-out-expo {{ $rate >= 70 ? 'bg-success-500' : 'bg-warning-500' }}"
                 style="width: {{ $rate }}%"></div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            @foreach ($rateGroups as $key)
                @php($g = $groups[$key] ?? ['total' => 0, 'deleted' => 0])
                <div class="rounded-xl border border-surface-border p-3.5">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="w-2 h-2 rounded-full shrink-0 {{ $groupDot[$key] }}"></span>
                        <p class="text-sm font-medium text-gray-600 dark:text-ink-muted truncate">{{ __("dashboard.group_{$key}") }}</p>
                        <x-edz.tooltip :label="__('dashboard.group_'.$key.'_hint')" side="top"
                            class="shrink-0 text-gray-400 transition-colors hover:text-gray-600 dark:text-ink-muted/70 dark:hover:text-ink-muted">
                            <x-edz.icon name="info-circle" class="w-4 h-4" />
                        </x-edz.tooltip>
                    </div>
                    <div class="mt-2 flex items-center gap-2">
                        <p class="text-3xl font-bold leading-none tracking-tighter text-ink tabular-nums">{{ $g['total'] }}</p>
                        @if ($g['deleted'] > 0)
                            <x-edz.tooltip :label="__('dashboard.deleted_brackets', ['count' => $g['deleted']])" side="top"
                                class="inline-flex items-center gap-1 rounded-full bg-danger-surface px-1.5 py-0.5 text-xs font-semibold leading-none text-danger-fg-strong tabular-nums">
                                <x-edz.icon name="trash" class="w-3.5 h-3.5" />
                                <span>{{ $g['deleted'] }}</span>
                            </x-edz.tooltip>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <p class="mt-4 text-xs text-gray-600 dark:text-ink-muted">{{ __('dashboard.outside_rate') }}</p>
    @endif
</div>
