@php
    // M4 confirmation tab — the body of the global count / rate mode.
    // Every number comes from $confirmationStats (ConfirmationStatsQuery +
    // ConfirmationStatsReport), so the headline, the group cards and the rate
    // always answer the same scan.

    // The rate row (D2): the numerator/denominator groups plus the ones the
    // rate excludes. There is no delivery card — orders that moved past
    // confirmation stay inside `confirmed`, so the cards reconcile with the
    // rate. OTHER is the muted bucket that never enters the rate.
    $rateGroups = ['confirmed', 'canceled', 'pending', 'failed', 'duplicate', 'deferred', 'other'];

    // D3: one colour per group, defined once in config/analytics.php and read by
    // every block (these cards, the doughnut, the trend and the team rows).
    $groupDot = array_map(
        static fn (string $token): string => 'bg-'.$token,
        config('analytics.group_colors'),
    );

    $groups = $confirmationStats['groups'] ?? [];
    $confirmed = (int) ($confirmationStats['confirmed'] ?? 0);
    $denominator = (int) ($confirmationStats['denominator'] ?? 0);
    $rate = (int) ($confirmationStats['rate'] ?? 0);
    $totalOrders = collect($groups)->sum('total');
    $trashOrders = collect($groups)->sum('deleted');

    // PHASE 37-N2: the date basis names the rate (gross vs net) and its wording;
    // the exclusions mark the groups they removed and add a footnote.
    $kind = (string) ($confirmationStats['kind'] ?? 'gross');
    $excluded = (int) ($confirmationStats['excluded'] ?? 0);
    $outside = $confirmationStats['outside'] ?? [];
    $rateLabel = __($kind === 'net' ? 'dashboard.confirmation_rate_net' : 'dashboard.confirmation_rate_gross');
    $rateExplainer = __($kind === 'net' ? 'dashboard.confirmation_rate_net_explainer' : 'dashboard.confirmation_rate_gross_explainer', ['confirmed' => $confirmed, 'denominator' => $denominator, 'rate' => $rate]);
    $rateBasis = __($kind === 'net' ? 'dashboard.confirmation_rate_net_basis' : 'dashboard.confirmation_rate_gross_basis', ['confirmed' => $confirmed, 'denominator' => $denominator]);
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
                <p class="text-sm font-medium uppercase tracking-wider text-ink-muted">{{ __('dashboard.total_orders') }}</p>
                <p class="mt-2 text-5xl font-bold leading-none tracking-tighter text-ink">{{ $totalOrders }}</p>
                <p class="mt-2 text-sm text-ink-muted">{{ __('dashboard.confirmed_of_total', ['total' => $totalOrders]) }}, {{ __('dashboard.group_confirmed') }}: {{ $confirmed }}</p>
            </div>
            <div class="rounded-2xl bg-surface-secondary/60 p-6 text-center">
                <span class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-danger-surface text-danger-fg-strong shadow-sm">
                    <x-edz.icon name="trash" class="h-6 w-6" />
                </span>
                <p class="text-sm font-medium uppercase tracking-wider text-ink-muted">{{ __('dashboard.trash_orders') }}</p>
                <p @class([
                    'mt-2 text-5xl font-bold leading-none tracking-tighter',
                    'text-danger-600' => $trashOrders > 0,
                    'text-ink-muted' => $trashOrders === 0,
                ])>{{ $trashOrders }}</p>
                <p class="mt-2 text-sm text-ink-muted">{{ __('dashboard.deleted_brackets', ['count' => $trashOrders]) }}</p>
            </div>
        </div>
    @else
        {{-- Rate tab: the rate and its horizontal group row read full width,
             and the charts with the doughnut drop underneath. --}}
        {{-- The rate's one home is the KPI card; here we keep the large figure
             and its bar without a second badge (D3, de-duplication). --}}
        <div class="min-w-0">
            <p class="text-sm font-medium uppercase tracking-wider text-ink-muted">{{ $rateLabel }}</p>
            <div class="mt-1 flex items-center gap-2">
                <p class="text-5xl font-bold tracking-tighter text-ink leading-none">{{ $rate }}<span class="text-2xl font-semibold text-ink-muted">%</span></p>
                <x-edz.tooltip :label="$rateExplainer" side="top" maxWidth="360px"
                    class="text-ink-muted transition-colors hover:text-ink">
                    <x-edz.icon name="information-circle" class="w-5 h-5" />
                </x-edz.tooltip>
            </div>
            <p class="mt-2 text-sm text-ink-muted">{{ $rateBasis }}</p>
        </div>
        <div class="w-full bg-surface-secondary rounded-full h-2.5 overflow-hidden my-5">
            <div class="h-2.5 rounded-full transition-all duration-700 ease-out-expo {{ $rate >= config('analytics.targets.confirmation_rate') ? 'bg-success-500' : 'bg-warning-500' }}"
                 style="width: {{ $rate }}%"></div>
        </div>
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
            @foreach ($rateGroups as $key)
                @php
                    $g = $groups[$key] ?? ['total' => 0, 'deleted' => 0];
                    // A group that contributes nothing to the rate (the net basis
                    // leaves the desk buckets out; the gross basis drops a group
                    // once every one of its statuses is excluded) reads muted and
                    // carries the "outside the rate" tag.
                    $isOutside = (bool) ($outside[$key] ?? false);
                @endphp
                <div @class([
                    'rounded-2xl border border-surface-border p-4',
                    'bg-surface-secondary/40' => $isOutside,
                ])>
                    <div class="flex items-center gap-2">
                        <span class="h-2 w-2 shrink-0 rounded-full {{ $groupDot[$key] ?? 'bg-ink-muted' }}"></span>
                        <p class="truncate text-sm font-medium text-ink-muted">{{ __("dashboard.group_{$key}") }}</p>
                        <x-edz.tooltip :label="__('dashboard.group_'.$key.'_hint')" side="top"
                            class="shrink-0 text-ink-muted transition-colors hover:text-ink">
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
                    @if ($isOutside)
                        <span class="mt-3 inline-flex items-center rounded-full bg-surface-secondary px-2 py-0.5 text-[11px] font-medium text-ink-muted">
                            {{ __('dashboard.outside_rate') }}
                        </span>
                    @endif
                </div>
            @endforeach
        </div>
        @if ($excluded > 0)
            <p class="mt-4 text-sm text-ink-muted">{{ __('dashboard.excluded_from_rate', ['count' => $excluded]) }}</p>
        @endif
    @endif
</div>
