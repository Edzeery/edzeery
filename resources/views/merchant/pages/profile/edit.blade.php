<x-merchant.body>
    @slot('title')
        {{ __('titles.profile') }}
    @endslot

    <form action="{{ route('profile.update') }}" method="POST"
        class="space-y-4 bg-white dark:bg-gray-800 p-6 rounded shadow">
        @csrf
        @method('PATCH')

        <div>
            <label class="block text-sm font-medium">{{ __('titles.name') }}</label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}"
                class="mt-1 block w-full border rounded px-3 py-2 bg-gray-50 dark:bg-gray-900">
        </div>

        <div>
            <label class="block text-sm font-medium">{{ __('titles.email') }}</label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}"
                class="mt-1 block w-full border rounded px-3 py-2 bg-gray-50 dark:bg-gray-900">
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded hover:bg-primary-700">
                {{ __('buttons.update') }}
            </button>
        </div>
    </form>
</x-merchant.body>
