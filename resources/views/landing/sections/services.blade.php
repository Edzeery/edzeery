<section id="services" class="py-24 bg-gray-50 dark:bg-[#0a0a0a]">
    <div class="max-w-7xl mx-auto px-6">

        <div class="text-center mb-16">
            <h2
                data-aos="fade-up"
                class="text-title-md lg:text-title-lg font-bold text-ink"
            >
                {{ __('landing.services_title') }}
            </h2>
            <p
                data-aos="fade-up"
                data-aos-delay="100"
                class="mt-4 text-theme-xl text-gray-500 dark:text-gray-400 max-w-2xl mx-auto"
            >
                {{ __('landing.services_subtitle') }}
            </p>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach ([
                [
                    'icon' => 'phone-portrait-outline',
                    'title' => __('landing.service_landing_builder'),
                    'desc' => __('landing.service_landing_builder_desc'),
                    'color' => 'brand',
                ],
                [
                    'icon' => 'storefront-outline',
                    'title' => __('landing.service_ecommerce'),
                    'desc' => __('landing.service_ecommerce_desc'),
                    'color' => 'green',
                ],
                [
                    'icon' => 'people-outline',
                    'title' => __('landing.service_crm'),
                    'desc' => __('landing.service_crm_desc'),
                    'color' => 'blue-light',
                ],
                [
                    'icon' => 'calculator-outline',
                    'title' => __('landing.service_erp'),
                    'desc' => __('landing.service_erp_desc'),
                    'color' => 'purple',
                ],
                [
                    'icon' => 'calendar-outline',
                    'title' => __('landing.service_hr'),
                    'desc' => __('landing.service_hr_desc'),
                    'color' => 'orange',
                ],
                [
                    'icon' => 'wallet-outline',
                    'title' => __('landing.service_accounting'),
                    'desc' => __('landing.service_accounting_desc'),
                    'color' => 'gray',
                ],
            ] as $feature)
                <div
                    data-aos="fade-up"
                    data-aos-delay="{{ $loop->index * 80 }}"
                    class="group rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-8 transition hover:shadow-lg hover:border-brand-300 dark:hover:border-brand-700"
                >
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-{{ $feature['color'] }}-50 dark:bg-{{ $feature['color'] }}-950/40 mb-5">
                        <ion-icon name="{{ $feature['icon'] }}" class="text-{{ $feature['color'] }}-600 dark:text-{{ $feature['color'] }}-400 text-xl"></ion-icon>
                    </div>

                    <h3 class="text-lg font-semibold text-ink mb-2">
                        {{ $feature['title'] }}
                    </h3>

                    <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                        {{ $feature['desc'] }}
                    </p>
                </div>
            @endforeach
        </div>

    </div>
</section>
