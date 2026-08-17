<?php

use function Livewire\Volt\layout;
use function Livewire\Volt\with;

layout('components.layouts.panel');

with([
    'stats' => [
        ['label' => 'Revenue', 'value' => '$48,250', 'delta' => '+12.5%', 'up' => true],
        ['label' => 'Orders', 'value' => '1,284', 'delta' => '+4.1%', 'up' => true],
        ['label' => 'Products', 'value' => '356', 'delta' => '-0.8%', 'up' => false],
        ['label' => 'Customers', 'value' => '9,017', 'delta' => '+9.3%', 'up' => true],
    ],
    'orders' => [
        ['#EDZ-1042', 'Omar Hassan', '$129.00', 'paid'],
        ['#EDZ-1041', 'Layla Mansour', '$84.50', 'pending'],
        ['#EDZ-1040', 'Yousef Nabil', '$249.99', 'shipped'],
        ['#EDZ-1039', 'Nour Adel', '$45.00', 'cancelled'],
        ['#EDZ-1038', 'Sara Khaled', '$310.20', 'paid'],
    ],
]);
?>

<div>
    <x-edz.page-header title="Dashboard" description="Welcome back, {{ auth()->user()?->name ?? 'guest' }}.">
        <x-slot:actions>
            <button type="button" class="edz-btn edz-btn--secondary edz-btn--sm">Export</button>
            <button type="button" class="edz-btn edz-btn--primary edz-btn--sm">New Report</button>
        </x-slot:actions>
    </x-edz.page-header>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($stats as $stat)
            <div class="edz-card edz-card--padded">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-gray-500">{{ $stat['label'] }}</p>
                    <span class="edz-badge edz-badge--{{ $stat['up'] ? 'success' : 'danger' }} edz-badge--dot">
                        {{ $stat['delta'] }}
                    </span>
                </div>
                <p class="mt-3 text-2xl font-bold tracking-tight text-gray-900">{{ $stat['value'] }}</p>
                <p class="mt-1 text-xs text-gray-400">vs. last month</p>
            </div>
        @endforeach
    </div>

    <div class="edz-card mt-6">
        <div class="edz-card__header">
            <div>
                <h2 class="edz-card__title">Recent orders</h2>
                <p class="text-sm text-gray-400">Latest transactions across your stores</p>
            </div>
            <a href="#" class="edz-btn edz-btn--ghost edz-btn--sm">View all</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 text-start text-xs uppercase tracking-wider text-gray-400">
                        <th class="px-4 py-3 text-start font-semibold">Order</th>
                        <th class="px-4 py-3 text-start font-semibold">Customer</th>
                        <th class="px-4 py-3 text-start font-semibold">Total</th>
                        <th class="px-4 py-3 text-start font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $order)
                        <tr class="border-b border-gray-100 last:border-0">
                            <td class="px-4 py-3 font-medium text-gray-700">{{ $order[0] }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $order[1] }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $order[2] }}</td>
                            <td class="px-4 py-3">
                                <span class="edz-badge edz-badge--{{ $order[3] === 'paid' || $order[3] === 'shipped' ? 'success' : ($order[3] === 'pending' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($order[3]) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
