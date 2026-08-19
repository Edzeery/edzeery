<?php

use App\Enums\Store\StorePermissionEnum;
use App\Models\Orders\Order;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;

layout('components.layouts.store');

state([
    'statusFilter' => '',
    'search'       => '',
    'orders'       => [],
    'page'         => 1,
]);

mount(function (): void {
    abort_unless(canStore(StorePermissionEnum::ORDER_VIEW->value), 403);
    $this->loadOrders();
});

$loadOrders = function () {
    $query = Order::where('store_id', currentStoreId())
        ->with(['customer', 'status', 'items.variant']);

    if ($this->statusFilter) {
        $query->whereHas('status', fn ($q) => $q->where('key', $this->statusFilter));
    }

    if ($this->search) {
        $query->where(function ($q) {
            $q->where('number', 'like', "%{$this->search}%")
              ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', "%{$this->search}%")
                  ->orWhere('phone', 'like', "%{$this->search}%"));
        });
    }

    $this->orders = $query->orderByDesc('created_at')->paginate(15, ['*'], 'page', $this->page)->toArray();
};

$setPage = function (int $page) {
    $this->page = $page;
    $this->loadOrders();
};

$confirm = function (string $orderId) {
    abort_unless(canStore(StorePermissionEnum::ORDER_CONFIRM->value), 403);

    $order = Order::findOrFail($orderId);
    app(\App\Domains\Order\Services\OrderService::class)->confirm($order);

    $this->page = 1;
    $this->loadOrders();
    $this->dispatch('swal', type: 'success', title: 'Order #' . $order->number . ' confirmed');
};

$prepare = function (string $orderId) {
    abort_unless(canStore(StorePermissionEnum::ORDER_MANAGE->value), 403);

    $order = Order::findOrFail($orderId);
    app(\App\Domains\Order\Services\OrderService::class)->startPreparing($order);

    $this->page = 1;
    $this->loadOrders();
};

$ship = function (string $orderId) {
    abort_unless(canStore(StorePermissionEnum::ORDER_MANAGE->value), 403);

    $order = Order::findOrFail($orderId);
    app(\App\Domains\Order\Services\OrderService::class)->ship($order);

    $this->page = 1;
    $this->loadOrders();
};

$deliver = function (string $orderId) {
    abort_unless(canStore(StorePermissionEnum::ORDER_MANAGE->value), 403);

    $order = Order::findOrFail($orderId);
    app(\App\Domains\Order\Services\OrderService::class)->deliver($order);

    $this->page = 1;
    $this->loadOrders();
};

$cancel = function (string $orderId) {
    abort_unless(canStore(StorePermissionEnum::ORDER_CANCEL->value), 403);

    $order = Order::findOrFail($orderId);
    app(\App\Domains\Order\Services\OrderService::class)->cancel($order, 'Cancelled by merchant');

    $this->page = 1;
    $this->loadOrders();
};

$refreshOrders = function () {
    $this->loadOrders();
};
?>

<div>
    <x-edz.page-header
        title="{{ __('merchant_panel.orders') }}"
        description="{{ __('Manage customer orders') }}">
    </x-edz.page-header>

    <div class="flex flex-wrap gap-3 mb-6">
        <select wire:model.live="statusFilter" wire:change="$wire.refreshOrders()" class="edz-input text-sm w-auto">
            <option value="">{{ __('All Statuses') }}</option>
            <option value="pending">{{ __('Pending') }}</option>
            <option value="confirmed">{{ __('Confirmed') }}</option>
            <option value="preparing">{{ __('Preparing') }}</option>
            <option value="shipped">{{ __('Shipped') }}</option>
            <option value="delivered">{{ __('Delivered') }}</option>
            <option value="cancelled">{{ __('Cancelled') }}</option>
        </select>

        <input
            type="text"
            wire:model.live="search"
            wire:input.debounce.300ms="$wire.refreshOrders()"
            placeholder="{{ __('Search by number, name or phone...') }}"
            class="edz-input text-sm"
        >
    </div>

    <div class="edz-card overflow-hidden">
        <div class="relative">
            <div wire:loading class="absolute inset-0 z-10 bg-surface/80 backdrop-blur-sm p-4 space-y-3 overflow-hidden" wire:target="statusFilter,search">
                @for ($i = 0; $i < 5; $i++)
                    <div class="flex items-center gap-4 py-2">
                        <x-edz.skeleton width="5rem" height="0.875rem" />
                        <div class="flex-1 space-y-1.5">
                            <x-edz.skeleton width="40%" />
                            <x-edz.skeleton width="6rem" height="0.75rem" />
                        </div>
                        <x-edz.skeleton width="5rem" height="1.5rem" rounded="full" />
                        <x-edz.skeleton width="4rem" />
                        <x-edz.skeleton width="6rem" height="0.75rem" />
                        <x-edz.skeleton width="8rem" />
                    </div>
                @endfor
            </div>

            <div wire:loading.class="opacity-40 pointer-events-none" wire:target="statusFilter,search">
                @if(!empty($orders['data']))
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-secondary">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-muted uppercase">{{ __('Number') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-muted uppercase">{{ __('Customer') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-muted uppercase">{{ __('table.status') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-muted uppercase">{{ __('Total') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-muted uppercase">{{ __('Date') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-ink-muted uppercase">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-100 dark:divide-ink-800">
                        @foreach($orders['data'] as $order)
                            @php
                                $service = app(\App\Domains\Order\Services\OrderService::class);
                                $transitions = $service->availableTransitions(Order::findOrFail($order['id']));
                            @endphp
                            <tr class="hover:bg-surface-50 dark:hover:bg-ink-800/50">
                                <td class="px-4 py-3 font-mono font-semibold text-ink">
                                    #{{ $order['number'] }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-ink">{{ $order['customer']['name'] ?? '-' }}</div>
                                    <div class="text-xs text-ink-muted">{{ $order['customer']['phone'] ?? '' }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <x-merchant.status domain="order" :status="$order['status']['key'] ?? 'pending'" />
                                </td>
                                <td class="px-4 py-3 font-semibold text-ink">
                                    {{ currency($order['total_amount']) }}
                                </td>
                                <td class="px-4 py-3 text-ink-muted">
                                    {{ \Carbon\Carbon::parse($order['created_at'])->format('M d, Y') }}
                                </td>
                                <td class="px-4 py-3 text-right space-x-1">
                                    @if(in_array('confirmed', $transitions))
                                        <button x-data @click.prevent="if (await EdzSwal.confirmAction('{{ __('Confirm') }}', '{{ __('messages.confirm_order_action', ['action' => __('Confirm')]) }}')) $wire.confirm('{{ $order['id'] }}')" class="edz-btn edz-btn--success edz-btn--xs">
                                            {{ __('Confirm') }}
                                        </button>
                                    @endif
                                    @if(in_array('preparing', $transitions))
                                        <button x-data @click.prevent="if (await EdzSwal.confirmAction('{{ __('Prepare') }}', '{{ __('messages.confirm_order_action', ['action' => __('Prepare')]) }}')) $wire.prepare('{{ $order['id'] }}')" class="edz-btn edz-btn--info edz-btn--xs">
                                            {{ __('Prepare') }}
                                        </button>
                                    @endif
                                    @if(in_array('shipped', $transitions))
                                        <button x-data @click.prevent="if (await EdzSwal.confirmAction('{{ __('Ship') }}', '{{ __('messages.confirm_order_action', ['action' => __('Ship')]) }}')) $wire.ship('{{ $order['id'] }}')" class="edz-btn edz-btn--primary edz-btn--xs">
                                            {{ __('Ship') }}
                                        </button>
                                    @endif
                                    @if(in_array('delivered', $transitions))
                                        <button x-data @click.prevent="if (await EdzSwal.confirmAction('{{ __('Deliver') }}', '{{ __('messages.confirm_order_action', ['action' => __('Deliver')]) }}')) $wire.deliver('{{ $order['id'] }}')" class="edz-btn edz-btn--success edz-btn--xs">
                                            {{ __('Deliver') }}
                                        </button>
                                    @endif
                                    @if(in_array('cancelled', $transitions))
                                        <button x-data @click.prevent="if (await EdzSwal.confirmAction('{{ __('Cancel') }}', '{{ __('messages.confirm_order_action', ['action' => __('Cancel')]) }}')) $wire.cancel('{{ $order['id'] }}')" class="edz-btn edz-btn--danger edz-btn--xs">
                                            {{ __('Cancel') }}
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4">
                {{-- Simple page navigation --}}
                @if($orders['last_page'] > 1)
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-ink-muted">
                            {{ __('Showing') }} {{ $orders['from'] ?? 0 }}-{{ $orders['to'] ?? 0 }} {{ __('of') }} {{ $orders['total'] }}
                        </span>
                        <div class="flex gap-1">
                            @if($orders['current_page'] > 1)
                                <button wire:click="$wire.setPage({{ $orders['current_page'] - 1 }})" class="edz-btn edz-btn--ghost edz-btn--xs">
                                    &laquo; {{ __('Previous') }}
                                </button>
                            @endif
                            @foreach(range(1, $orders['last_page']) as $page)
                                <button wire:click="$wire.setPage({{ $page }})"
                                    class="edz-btn edz-btn--xs {{ $page === $orders['current_page'] ? 'edz-btn--primary' : 'edz-btn--ghost' }}">
                                    {{ $page }}
                                </button>
                            @endforeach
                            @if($orders['current_page'] < $orders['last_page'])
                                <button wire:click="$wire.setPage({{ $orders['current_page'] + 1 }})" class="edz-btn edz-btn--ghost edz-btn--xs">
                                    {{ __('Next') }} &raquo;
                                </button>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        @else
            <div class="p-8 text-center text-ink-muted">
                <x-edz.icon name="cart" class="w-12 h-12 mx-auto mb-3 text-ink-muted" />
                <p>{{ __('No orders found') }}</p>
            </div>
        @endif
        </div>
        </div>
    </div>
</div>
