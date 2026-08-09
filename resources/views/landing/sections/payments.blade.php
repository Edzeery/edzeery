<section class="py-20 bg-gray-50 dark:bg-[#0f0f0f]">
    <div class="max-w-6xl mx-auto px-6 text-center">

        <h2 class="text-3xl font-bold mb-4">
            {{ __('landing.payments_title') }}
        </h2>

        <p class="text-gray-500 mb-12">
            {{ __('landing.payments_subtitle') }}
        </p>

        <div class="flex flex-wrap justify-center gap-10 items-center">
            @foreach ([
                'logos:visa',
                'logos:mastercard',
                'logos:stripe',
                'logos:paypal',
                'cryptocurrency-color:usdt',
                'token-branded:binance',
                'arcticons:redotpay',
                'logos:google-pay',

            ] as $icon)
                <span
                    data-aos="zoom-in"
                    class="text-5xl opacity-80 hover:opacity-100 transition">
                    <span class="iconify" data-icon="{{ $icon }}"></span>
                </span>
            @endforeach
        </div>
    </div>
</section>
