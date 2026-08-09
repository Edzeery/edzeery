<x-merchant.body>
    @slot('title')
        {{ __('titles.dashboard') }}
    @endslot

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        {{-- إحصائيات --}}
        <div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow">
            <h2 class="text-sm font-medium text-gray-500">{{ __('titles.stores_count') }}</h2>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['stores_count'] }}</p>
        </div>

        <div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow">
            <h2 class="text-sm font-medium text-gray-500">{{ __('titles.active_stores') }}</h2>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['active_stores'] }}</p>
        </div>

        <div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow">
            <h2 class="text-sm font-medium text-gray-500">{{ __('titles.pending_stores') }}</h2>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['pending_stores'] }}</p>
        </div>

        <div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow">
            <h2 class="text-sm font-medium text-gray-500">{{ __('titles.team_members') }}</h2>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['memberships_count'] }}</p>
        </div>
    </div>

    {{-- قائمة المتاجر --}}
    <div class="mt-8">
        <h2 class="text-xl font-semibold mb-4">{{ __('titles.my_stores') }}</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($stores as $store)
                <div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow">
                    <div class="flex items-center justify-between">
                        <h3 class="font-semibold mx-2">{{ $store->name }}</h3>
                        <span class="text-xs px-2 py-0.5 rounded-full
                                     {{ $store->currentStatus()->value === 'active' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                            {{ $store->currentStatus()->getLabel() }}
                        </span>
                    </div>
                    <p class="text-sm mt-2 text-gray-500">{{ $store->latestSubscription()?->plan?->name ?? '-' }}</p>
                </div>
            @endforeach
        </div>
    </div>
</x-merchant.body>
