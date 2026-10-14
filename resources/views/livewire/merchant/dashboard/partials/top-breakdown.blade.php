{{-- PHASE 37-N7 (D5): the executive "where are the orders" pair. It moved
     out of the Delivery view and into Top, which is the only owner now; the
     caller gates the include on $isTopView && $canTopKpis. --}}
<div class="edz-stagger grid grid-cols-1 gap-6 lg:grid-cols-2 mb-6">
    {{-- Geographic Distribution --}}
    <div class="edz-card edz-card--padded">
        <h3 class="text-sm font-semibold tracking-tight text-ink mb-4">{{ __('dashboard.orders_by_state') }}</h3>
        @if ($ordersByState->isNotEmpty())
            <div class="space-y-2.5">
                @php($maxState = $ordersByState->max('count'))
                @foreach ($ordersByState as $state)
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-medium text-ink w-24 truncate" title="{{ $state->name }}">{{ $state->name }}</span>
                        <div class="flex-1 bg-surface-secondary rounded-full h-2 overflow-hidden">
                            <div class="h-2 rounded-full bg-accent-500 transition-all duration-700 ease-out-expo"
                                 style="width: {{ $maxState > 0 ? ($state->count / $maxState * 100) : 0 }}%"></div>
                        </div>
                        <span class="text-xs font-semibold text-ink w-8 text-right">{{ $state->count }}</span>
                    </div>
                @endforeach
            </div>
        @else
            <div class="h-32 flex items-center justify-center">
                <p class="text-sm text-ink-muted">{{ __('dashboard.no_data') }}</p>
            </div>
        @endif
    </div>
    {{-- Delivery Type --}}
    <div class="edz-card edz-card--padded">
        <h3 class="text-sm font-semibold tracking-tight text-ink mb-4">{{ __('dashboard.delivery_breakdown') }}</h3>
        @if ($deliveryTypeBreakdown->isNotEmpty())
            <div class="space-y-4 mt-6">
                @foreach ($deliveryTypeBreakdown as $dt)
                    @php($total = $deliveryTypeBreakdown->sum('count'))
                    @php($pct = $total > 0 ? round(($dt->count / $total) * 100) : 0)
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-sm font-medium text-ink">{{ $dt->delivery_type === 'home' ? __('orders.delivery_home') : __('orders.delivery_stopdesk') }}</span>
                            <span class="text-sm font-bold tracking-tight text-ink">{{ $pct }}%</span>
                        </div>
                        <div class="w-full bg-surface-secondary rounded-full h-2 overflow-hidden">
                            <div class="h-2 rounded-full transition-all duration-700 ease-out-expo {{ $dt->delivery_type === 'home' ? 'bg-accent-500' : 'bg-success-500' }}"
                                 style="width: {{ $pct }}%"></div>
                        </div>
                        <p class="mt-1 text-xs text-ink-muted">{{ $dt->count }} {{ __('orders.orders') }}</p>
                    </div>
                @endforeach
            </div>
        @else
            <div class="h-32 flex items-center justify-center">
                <p class="text-sm text-ink-muted">{{ __('dashboard.no_data') }}</p>
            </div>
        @endif
    </div>
</div>
