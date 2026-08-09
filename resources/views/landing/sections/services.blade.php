<section id="services" class="py-24 bg-white dark:bg-[#0a0a0a]">
    <div class="max-w-7xl mx-auto px-6">

        <h2 class="text-4xl font-bold text-center mb-4">
            {{ __('landing.services_title') }}
        </h2>

        <p class="text-center text-gray-500 mb-16 max-w-2xl mx-auto">
            {{ __('landing.services_subtitle') }}
        </p>

        <div class="grid md:grid-cols-3 gap-8">
            @foreach ([
                ['icon' => 'heroicon-o-building-storefront', 'title' => __('landing.service_store'), 'desc' => __('landing.service_store_desc')],
                ['icon' => 'heroicon-o-credit-card', 'title' => __('landing.service_payments'), 'desc' => __('landing.service_payments_desc')],
                ['icon' => 'heroicon-o-chart-bar', 'title' => __('landing.service_analytics'), 'desc' => __('landing.service_analytics_desc')],
            ] as $service)
                <div
                    data-aos="fade-up"
                    class="rounded-2xl border border-gray-200 dark:border-gray-800 p-8 bg-gray-50 dark:bg-[#111]">

                    <x-filament::icon
                        :name="$service['icon']"
                        class="w-10 h-10 text-primary mb-4"
                    />

                    <h3 class="text-xl font-semibold mb-2">
                        {{ $service['title'] }}
                    </h3>

                    <p class="text-gray-500 text-sm">
                        {{ $service['desc'] }}
                    </p>
                </div>
            @endforeach
        </div>
    </div>
</section>
