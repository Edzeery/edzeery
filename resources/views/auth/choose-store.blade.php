<x-layouts.guest :title="__('messages.select_store')">

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
                <div class="mb-6 rounded-xl border border-neutral-border dark:border-dark-border bg-neutral-secondary dark:bg-dark-secondary p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-ink">{{ __('stores.stores_used', ['used' => $effectiveUsage, 'max' => $isUnlimited ? '∞' : $maxStores]) }}</p>
                            <p class="mt-0.5 text-xs text-ink-muted">{{ __('plans.max_stores') }}: {{ $subscription->plan->name }}</p>
                        </div>
                        @if (! $canCreate)
                            <a href="{{ route('account.billing') }}"
                               class="inline-flex items-center gap-1.5 px-4 py-2 bg-brand-600 text-white text-xs font-semibold rounded-xl hover:bg-brand-700 shadow-sm shadow-brand-600/20 transition">
                                {{ __('stores.upgrade_plan') }}
                            </a>
                        @endif
                    </div>
                    @if (! $isUnlimited && $maxStores > 0)
                        <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-neutral-border dark:bg-dark-border">
                            <div class="h-full rounded-full bg-brand-600 transition-all duration-500 ease-out"
                                 style="width: {{ min(100, ($effectiveUsage / (int) $maxStores) * 100) }}%"></div>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Create Store --}}
            @if ($canCreate)
                <div class="mb-6 text-center">
                    <a href="{{ route('merchant.create-store') }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 bg-success-50 dark:bg-success-900/20 text-success-700 dark:text-success-300
                              text-sm font-semibold rounded-xl border border-success-200 dark:border-success-800
                              hover:bg-success-100 dark:hover:bg-success-900/30 transition-all duration-200">
                        <ion-icon name="add-circle-outline" class="text-lg"></ion-icon>
                        {{ __('buttons.create') }} {{ __('buttons.new') }}
                    </a>
                </div>
            @endif

            {{-- Stores List --}}
            <div class="space-y-3">

                @foreach ($stores as $item)
                    <form method="POST" action="{{ route('merchant.choose-store.select', $item->store) }}">
                        @csrf

                        <button type="submit"
                            class="w-full flex items-center justify-between p-4 rounded-xl
                                   border border-neutral-border dark:border-dark-border
                                   bg-neutral-secondary dark:bg-dark-secondary
                                   hover:border-brand-300 dark:hover:border-brand-600
                                   hover:shadow-sm transition-all duration-200 group">

                            {{-- Store Info --}}
                            <div class="text-left space-y-1">
                                <div class="font-semibold text-ink group-hover:text-brand-600 dark:group-hover:text-brand-400 transition">
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
                            <ion-icon name="chevron-forward-outline"
                                      class="text-lg text-brand-500 group-hover:translate-x-1 transition-transform duration-200"></ion-icon>
                        </button>
                    </form>
                @endforeach

            </div>

            {{-- Logout --}}
            <div class="mt-6 text-center">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-ink-muted
                                    hover:text-error-600 dark:hover:text-error-400 rounded-xl hover:bg-error-50 dark:hover:bg-error-900/10 transition-all duration-200">
                        <ion-icon name="log-out-outline" class="text-base"></ion-icon>
                        {{ __('buttons.logout') }}
                    </button>
                </form>
            </div>

        </x-auth.card>

    </div>

</x-layouts.guest>
