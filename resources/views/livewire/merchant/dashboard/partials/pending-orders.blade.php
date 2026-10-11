{{-- Pending confirmations (39-M5.1): the desk's live queue, promoted into the
     KPI row so an operator sees what to act on beside the headline numbers. --}}
<div class="edz-card edz-card--padded flex h-full flex-col">
    <div class="mb-4 flex items-center justify-between">
        <h3 class="text-sm font-semibold tracking-tight text-ink">{{ __('dashboard.pending_confirmation') }}</h3>
        @if ($pendingOrders->count() > 0)
            <a href="{{ route('merchant.orders.index', currentStore()) }}" wire:navigate
               class="text-xs font-medium text-accent-600 transition-colors hover:text-accent-500">
                {{ __('buttons.view_all') }}
            </a>
        @endif
    </div>
    @if ($pendingOrders->isNotEmpty())
        <div class="divide-y divide-surface-border">
            @foreach ($pendingOrders as $order)
                <div class="flex items-center justify-between py-3 first:pt-0 last:pb-0">
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-ink">#{{ $order->number }}</p>
                        <p class="text-xs text-ink-muted">{{ $order->customer_name ?? '-' }} · {{ $order->customer_phone ?? '-' }}</p>
                    </div>
                    <div class="text-end">
                        <p class="text-sm font-bold tracking-tight text-ink">{{ number_format($order->total_amount, 0) }} {{ __('stores.currency_symbol') }}</p>
                        <p class="text-xs text-ink-muted">{{ \Carbon\Carbon::parse($order->created_at)->diffForHumans() }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="flex flex-1 flex-col items-center justify-center py-8 text-center">
            <x-edz.icon name="checkmark-circle-outline" class="mb-2 h-10 w-10 text-ink-muted" />
            <p class="text-sm text-ink-muted">{{ __('dashboard.no_pending_orders') }}</p>
        </div>
    @endif
</div>
