<section id="pricing" class="py-24 bg-gray-50 dark:bg-[#0f0f0f]" x-data="{ billing: 'monthly' }">
    <div class="max-w-7xl mx-auto px-6">

        {{-- Title --}}
        <h2 class="text-4xl font-bold text-center mb-12">
            {{ __('landing.pricing') }}
        </h2>

        {{-- Toggle Monthly / Yearly --}}
        <div class="flex justify-center mb-14">
            <div class="flex border rounded-xl p-1 bg-gray-100 dark:bg-gray-800 shadow-sm">
                <button @click="billing = 'monthly'"
                    :class="billing === 'monthly' ? 'bg-primary  scale-105 font-semibold' :
                        'text-gray-700 dark:text-gray-300'"
                    class="px-6 py-2 rounded-lg text-sm transition-all duration-200 cursor-pointer">
                    {{ __('landing.monthly') ?? 'شهري' }}
                </button>
                <button @click="billing = 'yearly'"
                    :class="billing === 'yearly' ? 'bg-primary  scale-105 font-semibold' :
                        'text-gray-700 dark:text-gray-300'"
                    class="px-6 py-2 rounded-lg text-sm transition-all duration-200 cursor-pointer">
                    {{ __('landing.yearly') ?? 'سنوي' }}
                </button>
            </div>
        </div>

        {{-- Plans Grid --}}
        <div class="grid md:grid-cols-4 gap-8">
            @foreach ($plans as $plan)
                @php
                    $monthly = $plan->prices->firstWhere('billing_period', 'monthly');
                    $yearly = $plan->prices->firstWhere('billing_period', 'yearly');
                    $hasTrial = $plan->trial_days > 0;

                    $currency = match (app()->getLocale()) {
                        'ar' => 'DZD',
                        'en' => 'USD',
                        default => 'EUR',
                    };

                    $formatPrice = fn($price) => app()->getLocale() === 'ar'
                        ? number_format($price) . ' ' . __('currency.' . $currency)
                        : __('currency.' . $currency) . number_format($price);
                @endphp

<x-filament::card
    x-show="!(billing === 'yearly' && {{ $hasTrial ? 'true' : 'false' }})"
    x-transition
    data-aos="fade-up"
    data-aos-delay="{{ $loop->index * 100 }}"
    class="relative overflow-hidden transition-all duration-300 p-6  rounded-xl
    {{ $hasTrial ? 'ring-2 ring-primary scale-[1.03] bg-gradient-to-b from-primary/10 to-transparent' : 'bg-white dark:bg-gray-800' }}">

                    {{-- Trial Badge فقط للشهري --}}
                    @if ($hasTrial)
                        <div x-show="billing === 'monthly'" x-transition
                            class="absolute top-4 left-1/2 -translate-x-1/2 z-10">
                            <x-filament::badge color="primary">
                                {{ __('landing.free_plan') }}
                            </x-filament::badge>
                        </div>
                    @endif

                    {{-- Plan Name --}}
                    <h3 class="text-2xl font-bold mt-4 text-center">
                        <span x-show="billing === 'monthly'">
                            {{ $hasTrial ? $plan->trial_days . ' ' . __('plans.' . $plan->slug) : __('plans.' . $plan->slug) }}
                        </span>

                        <span x-show="billing === 'yearly'">
                            {{ __('plans.' . $plan->slug) }}
                        </span>
                    </h3>

                    {{-- Trial description --}}
                    @if ($hasTrial)
                        <p x-show="billing === 'monthly'" x-transition class="mt-2 text-sm text-primary text-center">
                            {{ __('landing.best_for_start') ?? 'الأفضل للبدء بدون التزام' }}
                        </p>
                    @endif

                    {{-- Dynamic Price --}}
                    <div class="my-6 text-center">
                        <p class="mt-4 flex items-baseline justify-center gap-x-2" x-show="billing === 'monthly'">
                            <span class="text-4xl font-semibold tracking-tight">
                                {{ $formatPrice($monthly->price) }}
                            </span>
                            <span class="text-base text-gray-400">
                                / {{ __('landing.monthly') }}
                            </span>
                        </p>

                        <p class="mt-4 flex items-baseline justify-center gap-x-2" x-show="billing === 'yearly'">
                            <span class="text-4xl font-semibold tracking-tight">
                                {{ $formatPrice($yearly->price) }}
                            </span>
                            <span class="text-base text-gray-400">
                                / {{ __('landing.yearly') }}
                            </span>
                        </p>

                        {{-- Savings --}}
                        @if ($yearly && $monthly && !$hasTrial)
                            <p x-show="billing === 'yearly'" class="text-xs text-success mt-1">
                                {{ __('landing.save_more') ?? 'وفر حتى 20٪ مع الدفع السنوي' }}
                            </p>
                        @endif
                    </div>

                    {{-- Features --}}
                    <ul class="space-y-2 text-sm mb-6">
                        @foreach ($plan->features as $feature)
                            <li class="flex justify-between">
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
                    <a x-bind:href="'{{ route('register') }}?plan={{ $plan->id }}&billing=' + billing"
                        class="mt-8 block rounded-md bg-{{ $plan->is_default ? 'green' : 'gray' }}-500 px-3.5 py-2.5 text-center text-sm font-semibold text-white hover:bg-{{ $plan->is_default ? 'green' : 'gray' }}-400 transition sm:mt-10">

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
