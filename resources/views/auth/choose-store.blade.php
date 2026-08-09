<x-layouts.app :title="__('messages.select_store')">

    <div class="min-h-[70vh] flex items-center justify-center px-4">

        <x-auth.card :title="__('messages.choose_store_title')" :subtitle="__('messages.choose_store_desc')">

            {{-- Create Store --}}
            @if (currentStore()?->canCreateMultiStore())
                <div class="mb-6 text-center">
                    <x-nav-link href="{{ route('filament.merchant.tenant.registration') }}"
                        class="inline-flex items-center gap-2 text-success hover:underline">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0
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

                        <button type="submit"
                            class="
                                w-full flex items-center justify-between
                                p-4 rounded-xl
                                border border-neutral-border dark:border-dark-border
                                bg-neutral-secondary dark:bg-dark-secondary
                                hover:bg-brand-soft dark:hover:bg-accent-strong
                                transition shadow-soft
                            ">
                            {{-- Store Info --}}
                            <div class="text-left space-y-1">
                                <div class="font-semibold text-neutral-text dark:text-dark-text">
                                    {{ $membership->store->name }}
                                </div>

                                <div class="text-xs text-neutral-soft dark:text-dark-soft">
                                    @php
                                        $role = in_array(
                                            $membership->user?->merchantRole->first()->name,
                                            App\Enums\Platform\UserRoleEnum::values(),
                                        )
                                            ? App\Enums\Platform\UserRoleEnum::from(
                                                $membership->user?->merchantRole->first()->name,
                                            )
                                            : App\Enums\Store\StoreRoleEnum::from(
                                                $membership->user?->merchantRole->first()->name,
                                            );

                                    @endphp

                                    <x-role-badge :role="$role" />

                                </div>

                                <x-status-badge :status="$membership->store?->currentStatus()" />

                            </div>

                            {{-- Subscription --}}
                            <div class="text-right space-y-1">
                                <div class="font-semibold text-neutral-text dark:text-dark-text">
                                    {{ $membership->user->latestSubscription()?->plan?->name }}
                                </div>

                                @php
                                    $status =
                                        $membership->user->latestSubscription()?->status ??
                                        App\Enums\SubscriptionPayment\StatusSubscriptionEnum::PENDING;
                                @endphp

                                <div class="text-xs px-2 py-1 rounded-full {{ $status->css() }}">
                                    {{ $status?->getLabel() ?? $status }}
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

</x-layouts.app>
