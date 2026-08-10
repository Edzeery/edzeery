<x-merchant.body>
    @slot('title')
        {{ __('titles.stores') }}
    @endslot

    <div class="flex justify-end mb-4">
        <a href="{{ route('account.merchant.stores.create') }}" class="px-4 py-2 bg-primary-600 text-white rounded hover:bg-primary-700">
            {{ __('buttons.create_store') }}
        </a>
    </div>
<div class="min-h-[70vh] flex items-center justify-center px-4">

        <x-auth.card
            :title="__('messages.choose_store_title')"
            :subtitle="__('messages.choose_store_desc')"
        >

            {{-- Create Store --}}
            @if (currentStore()?->canCreateMultiStore())
                <div class="mb-6 text-center">
                    <x-nav-link
                        href="{{ route('filament.merchant.tenant.registration') }}"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0
                                  9 9 0 0 1 18 0Z" />
                        </svg>

                        {{ __('buttons.create') }} {{ __('buttons.new') }}
                    </x-nav-link>
                </div>
            @endif

            {{-- Stores List --}}
            <div class="space-y-3">

                @foreach ($memberships as $membership)
                    <form method="POST" action="{{ route('choose-store.select', $membership) }}">
                        @csrf

                        <button
                            type="submit"
                            class="
                                w-full flex items-center justify-between
                                p-4 rounded-xl
                                border border-neutral-border dark:border-dark-border
                                bg-neutral-secondary dark:bg-dark-secondary
                                hover:bg-brand-soft dark:hover:bg-accent-strong
                                transition shadow-soft
                            "
                        >
                            {{-- Store Info --}}
                            <div class="text-left space-y-1">
                                <div class="font-semibold text-ink">
                                    {{ $membership->store->name }}
                                </div>

                                <div class="text-xs text-neutral-soft dark:text-dark-soft">
                                    {{ $membership->role->key ?? 'Member' }}
                                </div>

                                <div class="text-xs text-neutral-soft dark:text-dark-soft">
                                    {{ $membership->store->status ?? App\Enums\StoreStatusEnum::PENDING }}
                                </div>
                            </div>

                            {{-- Subscription --}}
                            <div class="text-right space-y-1">
                                <div class="font-semibold text-ink">
                                    {{ $membership->store?->latestSubscription()?->plan?->name }}
                                </div>

                                @php
                                    $status = $membership->store?->latestSubscription()?->status;
                                @endphp

                                <div class="text-xs px-2 py-1 rounded-full {{ $status?->css() }}">
                                    {{ $status }}
                                </div>
                            </div>

                            {{-- Arrow --}}
                            <span class="text-brand font-bold text-lg">
                                →
                            </span>
                        </button>
                    </form>
                @endforeach

            </div>

            {{-- Logout --}}
            <div class="mt-6 text-center">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-danger-button class="text-sm">
                        {{ __('buttons.logout') }}
                    </x-danger-button>
                </form>
            </div>

        </x-auth.card>

    </div>
</x-merchant.body>
