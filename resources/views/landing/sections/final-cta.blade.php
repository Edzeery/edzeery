<section class="py-24">
    <div class="max-w-4xl mx-auto px-6 text-center">

        <div
            data-aos="zoom-in"
            class="rounded-3xl bg-gradient-to-br from-brand-600 to-brand-800 px-8 py-16 text-white"
        >
            <h2 class="text-title-sm lg:text-title-md font-bold mb-4">
                {{ __('landing.cta_title') }}
            </h2>

            <p class="text-theme-xl text-brand-100 max-w-xl mx-auto mb-10">
                {{ __('landing.cta_subtitle') }}
            </p>

            <div class="flex flex-wrap justify-center gap-4">
                <a
                    href="{{ route('register') }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-white px-6 py-3 text-sm font-semibold text-brand-700 shadow-sm transition hover:bg-brand-50"
                >
                    {{ __('landing.start_now') }}
                    <ion-icon name="arrow-forward-outline" class="text-lg"></ion-icon>
                </a>

                <a
                    href="{{ route('contact') }}"
                    class="inline-flex items-center gap-2 rounded-lg border border-white/30 px-6 py-3 text-sm font-semibold text-white transition hover:bg-white/10"
                >
                    {{ __('landing.contact_us') }}
                </a>
            </div>
        </div>

    </div>
</section>
