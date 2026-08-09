<x-merchant.body>
    @slot('title')
        {{ __('titles.billing') }}
    @endslot

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($stores as $store)
            <div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow">
                <h3 class="font-semibold">{{ $store->name }}</h3>
                <p class="text-sm text-gray-500 mt-1">{{ __('titles.current_plan') }}: {{ $store->latestSubscription()?->plan?->name ?? '-' }}</p>
                <p class="text-sm text-gray-500">{{ __('titles.billing_status') }}:
                    {{ $store->latestPayment()?->isPaid() ? __('titles.paid') : __('titles.unpaid') }}
                </p>
            </div>
        @endforeach
    </div>
</x-merchant.body>
