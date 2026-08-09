<x-merchant.body>
    @slot('title')
        {{ __('titles.team') }}
    @endslot

    @foreach($stores as $store)
        <div class="mb-8">
            <h2 class="text-xl font-semibold mb-4">{{ $store->name }} - {{ __('titles.team') }}</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($store->team as $member)
                    <div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow">
                        <p class="font-semibold">{{ $member->user->name }}</p>
                        <p class="text-sm text-gray-500">{{ $member->user->email }}</p>
                        <p class="text-xs mt-1 px-2 py-0.5 rounded-full bg-blue-100 text-blue-700">
                            {{ $member->isOwner() ? __('titles.owner') : ($member->isAdmin() ? __('titles.admin') : __('titles.manager')) }}
                        </p>

                        <div class="mt-2 flex justify-end gap-2">
                            <a href="{{ route('account.merchant.team.edit', $member) }}" class="px-2 py-1 text-sm bg-gray-200 dark:bg-gray-700 rounded hover:bg-gray-300">
                                {{ __('buttons.edit') }}
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</x-merchant.body>
