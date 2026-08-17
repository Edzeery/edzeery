<section id="pricing" class="py-24 bg-gray-50 dark:bg-[#0f0f0f]" x-data="{ billing: 'monthly' }">
    <div class="max-w-7xl mx-auto px-6">

        {{-- Title --}}
        <div class="text-center mb-14">
            <h2
                data-aos="fade-up"
                class="text-title-md lg:text-title-lg font-bold text-ink"
            >
                {{ __('landing.pricing') }}
            </h2>
            <p
                data-aos="fade-up"
                data-aos-delay="100"
                class="mt-4 text-theme-xl text-gray-500 dark:text-gray-400"
            >
                {{ __('landing.pricing_subtitle') }}
            </p>
        </div>

        {{-- Toggle Monthly / Yearly --}}
        <div
            data-aos="fade-up"
            data-aos-delay="200"
            class="flex justify-center mb-14"
        >
            <div class="flex border rounded-xl p-1 bg-gray-100 dark:bg-gray-800 shadow-sm">
                <button
                    @click="billing = 'monthly'"
                    :class="billing === 'monthly' ? 'bg-brand-600 text-white shadow-sm' : 'text-gray-700 dark:text-gray-300'"
                    class="px-6 py-2 rounded-lg text-sm font-medium transition-all duration-200 cursor-pointer"
                >
                    {{ __('landing.monthly') }}
                </button>
                <button
                    @click="billing = 'yearly'"
                    :class="billing === 'yearly' ? 'bg-brand-600 text-white shadow-sm' : 'text-gray-700 dark:text-gray-300'"
                    class="px-6 py-2 rounded-lg text-sm font-medium transition-all duration-200 cursor-pointer"
                >
                    {{ __('landing.yearly') }}
                </button>
            </div>
        </div>

        {{-- Plans Grid --}}
        @php
            $planCount = count($plans);
            $gridCols = $planCount > 3 ? '4' : ($planCount > 2 ? '3' : '2');
        @endphp
        <div class="grid md:grid-cols-{{ $gridCols }} gap-8">
            @foreach ($plans as $plan)
                @php
                    $monthly = $plan->prices->firstWhere('billing_period', 'monthly');
                    $yearly = $plan->prices->firstWhere('billing_period', 'yearly');
                    $hasTrial = $plan->trial_days > 0;
                    $isDefault = $plan->is_default;

                    $currency = match (app()->getLocale()) {
                        'ar' => 'DZD',
                        'en' => 'USD',
                        default => 'EUR',
                    };

                    $formatPrice = fn($price) => app()->getLocale() === 'ar'
                        ? number_format($price) . ' ' . __('currency.' . $currency)
                        : __('currency.' . $currency) . ' ' . number_format($price);
                @endphp

                <div
                    x-show="!(billing === 'yearly' && {{ $hasTrial ? 'true' : 'false' }})"
                    x-transition
                    data-aos="fade-up"
                    data-aos-delay="{{ $loop->index * 100 }}"
                    class="relative rounded-2xl border p-8 transition-all duration-300
                        {{ $isDefault
                            ? 'border-brand-300 dark:border-brand-700 bg-white dark:bg-gray-900 shadow-lg ring-1 ring-brand-200 dark:ring-brand-800'
                            : 'border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900'
                        }}"
                >
                    {{-- Popular badge --}}
                    @if ($isDefault)
                        <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                            <span class="inline-block rounded-full bg-brand-600 px-4 py-1 text-xs font-semibold text-white">
                                {{ __('landing.most_popular') }}
                            </span>
                        </div>
                    @endif

                    {{-- Trial Badge --}}
                    @if ($hasTrial)
                        <div x-show="billing === 'monthly'" x-transition class="mb-4 text-center">
                            <span class="inline-block rounded-full bg-green-100 dark:bg-green-900/40 px-3 py-1 text-xs font-semibold text-green-700 dark:text-green-300">
                                {{ __('landing.free_plan') }}
                            </span>
                        </div>
                    @endif

                    {{-- Plan Name --}}
                    <h3 class="text-xl font-bold text-ink text-center">
                        <span x-show="billing === 'monthly'">
                            {{ $hasTrial ? $plan->trial_days . ' ' . __('plans.' . $plan->slug) : __('plans.' . $plan->slug) }}
                        </span>
                        <span x-show="billing === 'yearly'">
                            {{ __('plans.' . $plan->slug) }}
                        </span>
                    </h3>

                    {{-- Trial description --}}
                    @if ($hasTrial)
                        <p x-show="billing === 'monthly'" x-transition class="mt-2 text-sm text-brand-600 dark:text-brand-400 text-center">
                            {{ __('landing.best_for_start') }}
                        </p>
                    @endif

                    {{-- Dynamic Price --}}
                    <div class="my-6 text-center">
                        <p class="flex items-baseline justify-center gap-x-2" x-show="billing === 'monthly'">
                            <span class="text-4xl font-bold tracking-tight text-ink">
                                @if ($monthly)
                                    {{ $formatPrice($monthly->price) }}
                                @else
                                    —
                                @endif
                            </span>
                            <span class="text-sm text-gray-400">
                                / {{ __('landing.monthly') }}
                            </span>
                        </p>

                        <p class="flex items-baseline justify-center gap-x-2" x-show="billing === 'yearly'">
                            <span class="text-4xl font-bold tracking-tight text-ink">
                                @if ($yearly)
                                    {{ $formatPrice($yearly->price) }}
                                @else
                                    —
                                @endif
                            </span>
                            <span class="text-sm text-gray-400">
                                / {{ __('landing.yearly') }}
                            </span>
                        </p>

                        {{-- Savings --}}
                        @if ($yearly && $monthly && $yearly->price < $monthly->price && !$hasTrial)
                            <p x-show="billing === 'yearly'" class="text-xs text-green-600 dark:text-green-400 mt-2">
                                {{ __('landing.save_more') }}
                            </p>
                        @endif
                    </div>

                    {{-- Features --}}
                    <ul class="space-y-3 text-sm mb-8">
                        @foreach ($plan->features as $feature)
                            <li class="flex items-center justify-between">
                                <span class="text-gray-600 dark:text-gray-400">{{ __($feature->name) }}</span>
                                <span class="font-semibold text-ink">
                                    @if ($feature->pivot->value === 'unlimited')
                                        ∞
                                    @elseif ($feature->type === 'boolean')
                                        @if ($feature->pivot->value)
                                            <ion-icon name="checkmark-circle" class="text-green-500 text-lg"></ion-icon>
                                        @else
                                            <ion-icon name="close-circle" class="text-gray-300 dark:text-gray-600 text-lg"></ion-icon>
                                        @endif
                                    @else
                                        {{ $feature->pivot->value }}
                                    @endif
                                </span>
                            </li>
                        @endforeach
                    </ul>

                    {{-- CTA --}}
                    <a
                        x-bind:href="'{{ route('register') }}?plan={{ $plan->id }}&billing=' + billing"
                        class="block w-full rounded-lg px-4 py-3 text-center text-sm font-semibold transition
                            {{ $isDefault
                                ? 'bg-brand-600 text-white hover:bg-brand-700 shadow-sm'
                                : 'bg-gray-100 dark:bg-gray-800 text-ink hover:bg-gray-200 dark:hover:bg-gray-700'
                            }}"
                    >
                        <span x-show="billing === 'monthly' && {{ $hasTrial ? 'true' : 'false' }}">
                            {{ __('landing.start_now') }}
                        </span>
                        <span x-show="billing === 'yearly' || !{{ $hasTrial ? 'true' : 'false' }}">
                            {{ __('landing.subscribe_now') }}
                        </span>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>
