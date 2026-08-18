<?php

use App\Enums\Store\StorePermissionEnum;
use App\Models\Orders\Order;
use Livewire\WithPagination;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;

layout('components.layouts.merchant');

state([
    'statusFilter' => '',
    'search'       => '',
    'orders'       => null,
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

    $this->orders = $query->orderByDesc('created_at')->paginate(15);
};

$confirm = function (string $orderId) {
    abort_unless(canStore(StorePermissionEnum::ORDER_CONFIRM->value), 403);

    $order = Order::findOrFail($orderId);
    app(\App\Domains\Order\Services\OrderService::class)->confirm($order);

    $this->loadOrders();
    session()->flash('success', 'Order #' . $order->number . ' confirmed');
};

$prepare = function (string $orderId) {
    abort_unless(canStore(StorePermissionEnum::ORDER_MANAGE->value), 403);

    $order = Order::findOrFail($orderId);
    app(\App\Domains\Order\Services\OrderService::class)->startPreparing($order);

    $this->loadOrders();
};

$ship = function (string $orderId) {
    abort_unless(canStore(StorePermissionEnum::ORDER_MANAGE->value), 403);

    $order = Order::findOrFail($orderId);
    app(\App\Domains\Order\Services\OrderService::class)->ship($order);

    $this->loadOrders();
};

$deliver = function (string $orderId) {
    abort_unless(canStore(StorePermissionEnum::ORDER_MANAGE->value), 403);

    $order = Order::findOrFail($orderId);
    app(\App\Domains\Order\Services\OrderService::class)->deliver($order);

    $this->loadOrders();
};

$cancel = function (string $orderId) {
    abort_unless(canStore(StorePermissionEnum::ORDER_CANCEL->value), 403);

    $order = Order::findOrFail($orderId);
    app(\App\Domains\Order\Services\OrderService::class)->cancel($order, 'Cancelled by merchant');

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

    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-green-700 dark:text-green-300 text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Filters --}}
    <div class="flex flex-wrap gap-3 mb-6">
        <select wire:model.live="statusFilter" wire:change="$wire.refreshOrders()" class="text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg">
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
            class="text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg"
        >
    </div>

    {{-- Orders Table --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        @if($orders && $orders->count())
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">{{ __('Number') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">{{ __('Customer') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">{{ __('table.status') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">{{ __('Total') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">{{ __('Date') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($orders as $order)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                <td class="px-4 py-3 font-mono font-semibold text-gray-900 dark:text-white">
                                    #{{ $order->number }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-gray-900 dark:text-white">{{ $order->customer?->name ?? '-' }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $order->customer?->phone }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <x-merchant.status domain="order" :status="$order->status?->key ?? 'pending'" />
                                </td>
                                <td class="px-4 py-3 font-semibold text-gray-900 dark:text-white">
                                    {{ currency($order->total_amount) }}
                                </td>
                                <td class="px-4 py-3 text-gray-500 dark:text-gray-400">
                                    {{ $order->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @php
                                        $service = app(\App\Domains\Order\Services\OrderService::class);
                                        $transitions = $service->availableTransitions($order);
                                    @endphp
                                    @if(in_array('confirmed', $transitions))
                                        <button wire:click="$wire.confirm('{{ $order->id }}')" class="text-xs bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 px-2 py-1 rounded hover:bg-green-200 dark:hover:bg-green-900/50">
                                            {{ __('Confirm') }}
                                        </button>
                                    @endif
                                    @if(in_array('preparing', $transitions))
                                        <button wire:click="$wire.prepare('{{ $order->id }}')" class="text-xs bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 px-2 py-1 rounded hover:bg-blue-200 dark:hover:bg-blue-900/50">
                                            {{ __('Prepare') }}
                                        </button>
                                    @endif
                                    @if(in_array('shipped', $transitions))
                                        <button wire:click="$wire.ship('{{ $order->id }}')" class="text-xs bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 px-2 py-1 rounded hover:bg-indigo-200 dark:hover:bg-indigo-900/50">
                                            {{ __('Ship') }}
                                        </button>
                                    @endif
                                    @if(in_array('delivered', $transitions))
                                        <button wire:click="$wire.deliver('{{ $order->id }}')" class="text-xs bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 px-2 py-1 rounded hover:bg-emerald-200 dark:hover:bg-emerald-900/50">
                                            {{ __('Deliver') }}
                                        </button>
                                    @endif
                                    @if(in_array('cancelled', $transitions))
                                        <button wire:click="$wire.cancel('{{ $order->id }}')" class="text-xs bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 px-2 py-1 rounded hover:bg-red-200 dark:hover:bg-red-900/50 ml-1">
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
                {{ $orders->links() }}
            </div>
        @else
            <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                {{ __('No orders found') }}
            </div>
        @endif
    </div>
</div>
