<section class="py-28 text-center">
    <h1
        data-aos="fade-up"
        data-aos-delay="100"
        class="text-5xl font-bold mb-6
        text-ink"
    >
        {{ __('landing.hero_title') }}
    </h1>

    <p
        data-aos="fade-up"
        data-aos-delay="250"
        class="text-xl text-gray-500 mb-10"
    >
        {{ __('landing.hero_subtitle') }}
    </p>

    <div data-aos="zoom-in" data-aos-delay="400">
        <x-filament::button color="primary" size="lg" tag="a" href="#pricing" class="bg-brand text-white hover:bg-brand-strong rounded-base px-4 py-2">
            {{ __('landing.start_now') }}
        </x-filament::button>
    </div>
</section>
