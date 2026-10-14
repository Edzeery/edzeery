{{-- Delivery body (PHASE 37-N7/N8, D10): the count/rate switch above the
     per-status breakdown. Every figure is folded from the same $deliveryStats
     scan as the KPI row, so nothing here can contradict the rates. --}}
@php
    $stats = $deliveryStats ?? [];
    $groups = $stats['groups'] ?? [];
    $delivered = (int) ($stats['delivered'] ?? 0);
    $excluded = (int) ($stats['excluded'] ?? 0);
    $totalOrders = (int) ($stats['total_orders'] ?? collect($groups)->sum('total'));
    $trashOrders = (int) ($stats['trash_orders'] ?? collect($groups)->sum('deleted'));
    $kind = (string) ($stats['kind'] ?? 'gross');
    $outside = $stats['outside'] ?? [];
    $denominator = (int) ($stats['denominator'] ?? 0);

    $rateLabel = __($kind === 'net' ? 'dashboard.delivery_rate_net' : 'dashboard.delivery_rate_gross');
    $rateBasis = __(
        $kind === 'net' ? 'dashboard.delivery_rate_net_basis' : 'dashboard.delivery_rate_gross_basis',
        ['delivered' => $delivered, 'denominator' => $denominator],
    );

    // The two buckets the rate measures lead, then the fulfilment pipeline.
    $buckets = \App\Domains\Analytics\Support\DeliveryStatsGroups::all();
@endphp

<div wire:key="delivery-stats-{{ $filter->hash() }}" class="edz-card edz-card--padded mb-6">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-2">
        <h3 class="text-base font-semibold tracking-tight text-ink sm:text-lg">{{ __('dashboard.delivery_stats_title') }}</h3>
        <span class="rounded-full bg-surface-secondary px-3 py-1 text-xs font-medium text-ink-muted">
            {{ $rateLabel }} · {{ $rateBasis }}
        </span>
    </div>

    @if ($deliveryTab === 'count')
        {{-- Count tab: the 30-second read — every fulfilment figure the view
             tracks, one card each. The per-status split (and the "outside the
             rate" tagging) lives on the rate tab. --}}
        @php
            $returned = (int) ($stats['returned'] ?? ($groups['returned']['total'] ?? 0));
            $inProgress = (int) ($groups['in_progress']['total'] ?? 0);

            $tones = [
                'accent' => 'bg-surface text-accent-600',
                'success' => 'bg-success-surface text-success-fg-strong',
                'danger' => 'bg-danger-surface text-danger-fg-strong',
                'warning' => 'bg-warning-surface text-warning-fg-strong',
            ];

            $cards = [
                ['key' => 'total', 'label' => __('dashboard.total_orders'), 'hint' => __('dashboard.delivery_total_hint'), 'value' => $totalOrders, 'icon' => 'cart', 'tone' => 'accent'],
                ['key' => 'delivered', 'label' => __('dashboard.delivered'), 'value' => $delivered, 'icon' => 'check-circle', 'tone' => 'success', 'hint' => __('dashboard.delivery_group_delivered_hint')],
                ['key' => 'returned', 'label' => __('dashboard.returned'), 'value' => $returned, 'icon' => 'arrow-uturn-left', 'tone' => 'danger', 'hint' => __('dashboard.delivery_group_returned_hint')],
                ['key' => 'in_progress', 'label' => __('dashboard.delivery_group_in_progress'), 'value' => $inProgress, 'icon' => 'truck', 'tone' => 'warning', 'hint' => __('dashboard.delivery_group_in_progress_hint')],
                ['key' => 'trash', 'label' => __('dashboard.trash_orders'), 'value' => $trashOrders, 'icon' => 'trash', 'tone' => 'danger', 'hint' => __('dashboard.delivery_trash_hint')],
            ];
        @endphp
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
            @foreach ($cards as $card)
                <div class="rounded-2xl bg-surface-secondary/60 p-5 text-center">
                    <span @class([
                        'mx-auto mb-3 flex h-11 w-11 items-center justify-center rounded-2xl shadow-sm',
                        $tones[$card['tone']],
                    ])>
                        <x-edz.icon :name="$card['icon']" class="h-5 w-5" />
                    </span>
                    <p class="flex items-center justify-center gap-1 text-xs font-medium uppercase tracking-wider text-ink-muted">
                        <span>{{ $card['label'] }}</span>
                        <x-edz.tooltip :label="$card['hint']" side="top" maxWidth="220px"
                            class="text-ink-muted transition-colors hover:text-ink">
                            <x-edz.icon name="info-circle" class="h-3.5 w-3.5" />
                        </x-edz.tooltip>
                    </p>
                    <p @class([
                        'mt-2 text-4xl font-bold leading-none tracking-tighter tabular-nums',
                        'text-ink' => $card['key'] !== 'trash',
                        'text-danger-600' => $card['key'] === 'trash' && $card['value'] > 0,
                        'text-ink-muted' => $card['key'] === 'trash' && $card['value'] === 0,
                    ])>{{ $card['value'] }}</p>
                </div>
            @endforeach
        </div>
    @else
        {{-- Rate tab: the per-status breakdown the two rate cards fold from.
             A bucket that contributes nothing to the current basis reads muted
             and carries the "outside the rate" tag. --}}
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
            @foreach ($buckets as $key)
                @php
                    $g = $groups[$key] ?? ['total' => 0, 'deleted' => 0];
                    $isOutside = (bool) ($outside[$key] ?? false);
                @endphp
                <div @class([
                    'rounded-2xl border border-surface-border p-4',
                    'bg-surface-secondary/40' => $isOutside,
                ])>
                    <div class="flex items-center gap-2">
                        <span class="h-2 w-2 shrink-0 rounded-full" style="background-color: {{ \App\Domains\Analytics\Support\GroupColor::css($key) }}"></span>
                        <p class="truncate text-sm font-medium text-ink-muted">{{ __("dashboard.delivery_group_{$key}") }}</p>
                        <x-edz.tooltip :label="__('dashboard.delivery_group_'.$key.'_hint')" side="top"
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
    @endif

    @if ($excluded > 0)
        <p class="mt-4 text-sm text-ink-muted">{{ __('dashboard.excluded_from_rate', ['count' => $excluded]) }}</p>
    @endif
</div>
