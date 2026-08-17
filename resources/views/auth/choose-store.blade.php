<x-layouts.app :title="__('messages.select_store')">

    <div class="min-h-[70vh] flex items-center justify-center px-4">

        <x-auth.card :title="__('messages.choose_store_title')" :subtitle="__('messages.choose_store_desc')">

            {{-- Store Usage --}}
            @php
                $user = auth()->user();
                $subscription = $user->latestSubscription();
                $featureService = app(\App\Domains\Plan\Services\FeatureUsageService::class);
                $storesFeature = $subscription?->plan?->features?->firstWhere('slug', 'stores_max');
                $maxStores = $subscription?->plan?->getFeatureValue('stores_max');
                $consumption = $storesFeature ? (int) $featureService->getConsumption($subscription, $storesFeature->id) : 0;
                $storeCount = $stores->count();
                $effectiveUsage = max($consumption, $storeCount);
                $isUnlimited = $maxStores === 'unlimited';
                $canCreate = $subscription ? $featureService->canUse($subscription, 'stores_max') : false;
            @endphp

            @if ($subscription && $maxStores)
                <div class="mb-6 rounded-lg border border-surface-border bg-surface-secondary/50 p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-ink">{{ __('stores.stores_used', ['used' => $effectiveUsage, 'max' => $isUnlimited ? '∞' : $maxStores]) }}</p>
                            <p class="mt-0.5 text-xs text-ink-muted">{{ __('plans.max_stores') }}: {{ $subscription->plan->name }}</p>
                        </div>
                        @if (! $canCreate)
                            <a href="#" class="edz-btn edz-btn--primary edz-btn--sm">{{ __('stores.upgrade_plan') }}</a>
                        @endif
                    </div>
                    @if (! $isUnlimited && $maxStores > 0)
                        <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-surface-border">
                            <div class="h-full rounded-full bg-brand-600 transition-all" style="width: {{ min(100, ($effectiveUsage / (int) $maxStores) * 100) }}%"></div>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Create Store --}}
            @if ($canCreate)
                <div class="mb-6 text-center">
                    <x-nav-link href="{{ route('merchant.create-store') }}"
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

                @foreach ($stores as $item)
                    <form method="POST" action="{{ route('choose-store.select', $item->store) }}">
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
                                <div class="font-semibold text-ink">
                                    {{ $item->store->name }}
                                </div>

                                <div class="text-xs">
                                    <x-role-badge :role="$item->role" />
                                </div>

                                <x-status-badge domain="general" :status="$item->store->currentStatus()->getLabel()" />

                            </div>

                            {{-- Subscription --}}
                            <div class="text-right space-y-1">
                                <div class="font-semibold text-ink">
                                    {{ $item->subscription?->plan?->name ?? '—' }}
                                </div>

                                @php
                                    $subStatus = $item->subscription?->status
                                        ?? App\Enums\SubscriptionPayment\StatusSubscriptionEnum::PENDING;
                                @endphp

                                <div class="text-xs">
                                    <x-status-badge domain="general" :status="$subStatus->getLabel()" />
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
