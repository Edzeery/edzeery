<section
    id="pricing"
    class="py-24 bg-gray-50 dark:bg-[#0f0f0f]"
    x-data="{ billing: 'monthly' }"
>
    <div class="max-w-7xl mx-auto px-6">

        {{-- Title --}}
        <div class="text-center max-w-2xl mx-auto mb-14">
            <h2 class="text-4xl font-bold">
                {{ __('landing.pricing') }}
            </h2>
            <p class="mt-3 text-gray-500 dark:text-gray-400">
                {{ __('landing.pricing_subtitle') ?? 'اختر الخطة المناسبة لنمو متجرك وأعمالك' }}
            </p>
        </div>

        {{-- Billing Toggle --}}
        <div class="flex justify-center mb-16">
            <div class="inline-flex rounded-2xl bg-white dark:bg-gray-900 shadow-lg border border-gray-200 dark:border-gray-700 p-1">
                <button
                    @click="billing = 'monthly'"
                    :class="billing === 'monthly'
                        ? 'bg-primary text-white shadow-md scale-105'
                        : 'text-gray-600 dark:text-gray-300'"
                    class="px-6 py-2 rounded-xl text-sm font-medium transition-all duration-300">
                    {{ __('landing.monthly') ?? 'شهري' }}
                </button>

                <button
                    @click="billing = 'yearly'"
                    :class="billing === 'yearly'
                        ? 'bg-primary text-white shadow-md scale-105'
                        : 'text-gray-600 dark:text-gray-300'"
                    class="px-6 py-2 rounded-xl text-sm font-medium transition-all duration-300 relative">
                    {{ __('landing.yearly') ?? 'سنوي' }}

                    <span class="absolute -top-2 -right-2 text-[10px] bg-green-500 text-white px-2 py-0.5 rounded-full">
                        -20%
                    </span>
                </button>
            </div>
        </div>

        {{-- Plans --}}
        <div class="grid md:grid-cols-4 gap-8 items-stretch">
            @foreach ($plans as $plan)
                @php
                    $monthly = $plan->prices->firstWhere('billing_period', 'monthly');
                    $yearly = $plan->prices->firstWhere('billing_period', 'yearly');

                    $hasTrial = $plan->is_trial;
                    $isYearlyFeatured = $plan->slug === 'basic';

                    $currency = $plan->currency ?? 'DZD';

                    $formatPrice = fn($price) => app()->getLocale() === 'ar'
                        ? number_format($price) . ' ' . __('currency.' . $currency)
                        : __('currency.' . $currency) . number_format($price);
                @endphp

                <x-filament::card
                    x-cloak
                    x-show="!(billing === 'yearly' && {{ $hasTrial ? 'true' : 'false' }})"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    data-aos="fade-up"
                    data-aos-delay="{{ $loop->index * 100 }}"
                    :class="billing === 'yearly' && {{ $isYearlyFeatured ? 'true' : 'false' }}
                        ? 'ring-2 ring-green-500 scale-[1.05] shadow-2xl border border-green-400'
                        : 'hover:shadow-xl hover:-translate-y-1'"
                    class="relative rounded-2xl overflow-hidden transition-all duration-300 p-6 bg-white dark:bg-gray-900 h-full flex flex-col">

                    {{-- Trial Badge --}}
                    @if ($hasTrial)
                        <div
                            x-show="billing === 'monthly'"
                            x-transition.scale
                            class="absolute top-4 left-1/2 -translate-x-1/2 z-10">
                            <x-filament::badge color="primary">
                                {{ __('landing.free_plan') }}
                            </x-filament::badge>
                        </div>
                    @endif

                    {{-- Featured Annual Badge --}}
                    @if ($isYearlyFeatured)
                        <div
                            x-show="billing === 'yearly'"
                            x-transition.scale
                            class="absolute top-4 right-4 z-10">
                            <x-filament::badge color="success">
                                {{ __('landing.best_value') ?? 'أفضل قيمة' }}
                            </x-filament::badge>
                        </div>
                    @endif

                    {{-- Plan Name --}}
                    <h3 class="text-2xl font-bold mt-6 text-center">
                        <span x-show="billing === 'monthly'">
                            {{ $hasTrial
                                ? $plan->trial_days . ' ' . __('plans.' . $plan->slug)
                                : __('plans.' . $plan->slug) }}
                        </span>

                        <span x-show="billing === 'yearly'">
                            {{ __('plans.' . $plan->slug) }}
                        </span>
                    </h3>

                    {{-- Trial Description --}}
                    @if ($hasTrial)
                        <p
                            x-show="billing === 'monthly'"
                            x-transition
                            class="mt-2 text-sm text-primary text-center">
                            {{ __('landing.best_for_start') ?? 'الأفضل للبدء بدون التزام' }}
                        </p>
                    @endif

                    {{-- Price --}}
                    <div class="my-8 text-center min-h-[80px]">
                        <p class="flex items-baseline justify-center gap-x-2" x-show="billing === 'monthly'">
                            <span class="text-4xl font-bold">
                                {{ $formatPrice($monthly->price) }}
                            </span>
                            <span class="text-base text-gray-400">
                                / {{ __('landing.monthly') }}
                            </span>
                        </p>

                        <p class="flex items-baseline justify-center gap-x-2" x-show="billing === 'yearly'">
                            <span class="text-4xl font-bold">
                                {{ $formatPrice($yearly->price) }}
                            </span>
                            <span class="text-base text-gray-400">
                                / {{ __('landing.yearly') }}
                            </span>
                        </p>

                        @if ($yearly && $monthly && !$hasTrial)
                            <p
                                x-show="billing === 'yearly'"
                                class="text-xs text-green-500 mt-2 font-semibold">
                                🎉 {{ __('landing.save_more') ?? 'وفر حتى 20٪ سنويًا' }}
                            </p>
                        @endif
                    </div>

                    {{-- Features --}}
                    <ul class="space-y-3 text-sm mb-8 flex-1">
                        @foreach ($plan->features as $feature)
                            <li class="flex justify-between border-b border-gray-100 dark:border-gray-800 pb-2">
                                <span>{{ __($feature->name) }}</span>
                                <span class="font-semibold">
                                    @if ($feature->pivot->value === 'unlimited')
                                        ∞
                                    @elseif ($feature->type === 'boolean')
                                        {{ $feature->pivot->value ? '✔' : '✖' }}
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
                        :class="billing === 'yearly' && {{ $isYearlyFeatured ? 'true' : 'false' }}
                            ? 'bg-green-600 hover:bg-green-500'
                            : '{{ $plan->is_default ? 'bg-primary hover:bg-primary/90' : 'bg-gray-700 hover:bg-gray-600' }}'"
                        class="mt-auto block rounded-xl px-4 py-3 text-center text-sm font-semibold text-white transition-all duration-300">

                        <span x-show="billing === 'monthly' && {{ $hasTrial ? 'true' : 'false' }}">
                            {{ __('landing.start_now') }}
                        </span>

                        <span x-show="billing === 'yearly' || !{{ $hasTrial ? 'true' : 'false' }}">
                            {{ __('landing.subscribe_now') }}
                        </span>
                    </a>
                </x-filament::card>
            @endforeach
        </div>
    </div>
</section>
