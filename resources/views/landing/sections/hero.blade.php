<section class="relative py-24 lg:py-32 overflow-hidden">
    {{-- Background gradient --}}
    <div class="absolute inset-0 -z-10 bg-gradient-to-b from-brand-50 to-transparent dark:from-brand-950/30 dark:to-transparent"></div>

    <div class="max-w-7xl mx-auto px-6">
        <div class="grid lg:grid-cols-2 gap-12 items-center">

            {{-- Left: Copy --}}
            <div>
                <h1
                    data-aos="fade-up"
                    data-aos-delay="100"
                    class="text-title-lg lg:text-title-xl font-bold text-ink leading-tight"
                >
                    {{ __('landing.hero_title') }}
                </h1>

                <p
                    data-aos="fade-up"
                    data-aos-delay="250"
                    class="mt-6 text-theme-xl text-gray-500 dark:text-gray-400 max-w-xl"
                >
                    {{ __('landing.hero_subtitle') }}
                </p>

                <div
                    data-aos="fade-up"
                    data-aos-delay="400"
                    class="mt-10 flex flex-wrap gap-4"
                >
                    {{-- Primary CTA --}}
                    <a
                        href="{{ route('register') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600"
                    >
                        {{ __('landing.start_now') }}
                        <ion-icon name="arrow-forward-outline" class="text-lg"></ion-icon>
                    </a>

                    {{-- Secondary CTA --}}
                    <a
                        href="#services"
                        class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-6 py-3 text-sm font-semibold text-ink shadow-sm transition hover:bg-gray-50 dark:hover:bg-gray-700"
                    >
                        {{ __('landing.watch_demo') }}
                        <ion-icon name="play-circle-outline" class="text-lg"></ion-icon>
                    </a>
                </div>

                {{-- Trust indicators --}}
                <div
                    data-aos="fade-up"
                    data-aos-delay="550"
                    class="mt-10 flex items-center gap-6 text-sm text-gray-500 dark:text-gray-400"
                >
                    <span class="flex items-center gap-1.5">
                        <ion-icon name="checkmark-circle" class="text-brand-500 text-lg"></ion-icon>
                        {{ __('landing.no_credit_card') }}
                    </span>
                    <span class="flex items-center gap-1.5">
                        <ion-icon name="checkmark-circle" class="text-brand-500 text-lg"></ion-icon>
                        {{ __('landing.free_trial') }}
                    </span>
                    <span class="flex items-center gap-1.5">
                        <ion-icon name="checkmark-circle" class="text-brand-500 text-lg"></ion-icon>
                        {{ __('landing.cancel_anytime') }}
                    </span>
                </div>
            </div>

            {{-- Right: Product illustration placeholder --}}
            <div
                data-aos="fade-left"
                data-aos-delay="300"
                class="relative hidden lg:block"
            >
                <div class="relative rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-2xl overflow-hidden">
                    {{-- Mock dashboard --}}
                    <div class="flex items-center gap-2 px-4 py-3 border-b border-gray-100 dark:border-gray-800">
                        <span class="w-3 h-3 rounded-full bg-red-400"></span>
                        <span class="w-3 h-3 rounded-full bg-yellow-400"></span>
                        <span class="w-3 h-3 rounded-full bg-green-400"></span>
                        <span class="ml-3 text-xs text-gray-400">edzeery.com/dashboard</span>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex gap-4">
                            <div class="flex-1 rounded-lg bg-brand-50 dark:bg-brand-950/40 p-4">
                                <div class="text-xs text-brand-600 dark:text-brand-400 font-medium">{{ __('landing.mock_revenue') }}</div>
                                <div class="mt-1 text-2xl font-bold text-ink">125,000 {{ __('currency.DZD') }}</div>
                            </div>
                            <div class="flex-1 rounded-lg bg-green-50 dark:bg-green-950/40 p-4">
                                <div class="text-xs text-green-600 dark:text-green-400 font-medium">{{ __('landing.mock_orders') }}</div>
                                <div class="mt-1 text-2xl font-bold text-ink">1,284</div>
                            </div>
                        </div>
                        <div class="rounded-lg bg-gray-50 dark:bg-gray-800 p-4">
                            <div class="text-xs text-gray-500 mb-2">{{ __('landing.mock_recent_orders') }}</div>
                            @foreach (range(1, 3) as $i)
                                <div class="flex items-center justify-between py-2 {{ $i < 3 ? 'border-b border-gray-100 dark:border-gray-700' : '' }}">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-brand-100 dark:bg-brand-900 flex items-center justify-center text-xs font-medium text-brand-700 dark:text-brand-300">
                                            #{{ rand(1000, 9999) }}
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-ink">{{ __('landing.mock_customer_' . $i) }}</div>
                                            <div class="text-xs text-gray-400">{{ rand(1, 5) }} {{ __('landing.mock_items') }}</div>
                                        </div>
                                    </div>
                                    <div class="text-sm font-semibold text-ink">{{ rand(2000, 15000) }} {{ __('currency.DZD') }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Floating badge --}}
                <div class="absolute -bottom-4 -left-4 rounded-xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-lg px-4 py-3 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-900/40 flex items-center justify-center">
                        <ion-icon name="trending-up" class="text-green-600 dark:text-green-400 text-xl"></ion-icon>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">{{ __('landing.mock_growth') }}</div>
                        <div class="text-sm font-bold text-green-600 dark:text-green-400">+24%</div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
