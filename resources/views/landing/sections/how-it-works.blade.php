<section class="py-24">
    <div class="max-w-7xl mx-auto px-6">

        <div class="text-center mb-16">
            <h2
                data-aos="fade-up"
                class="text-title-md lg:text-title-lg font-bold text-ink"
            >
                {{ __('landing.how_it_works_title') }}
            </h2>
            <p
                data-aos="fade-up"
                data-aos-delay="100"
                class="mt-4 text-theme-xl text-gray-500 dark:text-gray-400 max-w-2xl mx-auto"
            >
                {{ __('landing.how_it_works_subtitle') }}
            </p>
        </div>

        <div class="grid md:grid-cols-4 gap-8">
            @php
                $steps = [
                    [
                        'num' => '01',
                        'icon' => 'person-add-outline',
                        'title' => __('landing.step_register'),
                        'desc' => __('landing.step_register_desc'),
                    ],
                    [
                        'num' => '02',
                        'icon' => 'build-outline',
                        'title' => __('landing.step_setup'),
                        'desc' => __('landing.step_setup_desc'),
                    ],
                    [
                        'num' => '03',
                        'icon' => 'cube-outline',
                        'title' => __('landing.step_add_products'),
                        'desc' => __('landing.step_add_products_desc'),
                    ],
                    [
                        'num' => '04',
                        'icon' => 'rocket-outline',
                        'title' => __('landing.step_launch'),
                        'desc' => __('landing.step_launch_desc'),
                    ],
                ];
            @endphp

            @foreach ($steps as $step)
                <div
                    data-aos="fade-up"
                    data-aos-delay="{{ $loop->index * 120 }}"
                    class="relative text-center"
                >
                    {{-- Connector line (hidden on last) --}}
                    @if (!$loop->last)
                        <div class="hidden md:block absolute top-10 left-[60%] w-[80%] border-t-2 border-dashed border-gray-200 dark:border-gray-700"></div>
                    @endif

                    {{-- Step number --}}
                    <div class="relative inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-brand-50 dark:bg-brand-950/40 mb-6">
                        <ion-icon name="{{ $step['icon'] }}" class="text-brand-600 dark:text-brand-400 text-3xl"></ion-icon>
                        <span class="absolute -top-2 -right-2 w-7 h-7 rounded-full bg-brand-600 text-white text-xs font-bold flex items-center justify-center">
                            {{ $step['num'] }}
                        </span>
                    </div>

                    <h3 class="text-lg font-semibold text-ink mb-2">
                        {{ $step['title'] }}
                    </h3>

                    <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed max-w-xs mx-auto">
                        {{ $step['desc'] }}
                    </p>
                </div>
            @endforeach
        </div>

    </div>
</section>
