<!DOCTYPE html>
<html lang="{{ $lang ?? app()->getLocale() }}" dir="{{ $dir ?? 'ltr' }}" class="h-full scroll-smooth">

<head>
    <meta charset="UTF-8">
    <script>
        (function() {
            var t = localStorage.getItem('edz-theme');
            if (t === 'dark' || (!t && window.matchMedia && window.matchMedia('(prefers-color-scheme:dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="swal-i18n" content="{{ json_encode([
        'confirm_delete_title' => __('messages.action_confirm'),
        'confirm_delete' => __('messages.action_confirm_delete'),
        'confirm_delete_named' => __('messages.action_confirm_delete') . ' "{name}"?',
        'confirm_bulk_delete' => __('messages.ask_delete'),
        'delete' => __('buttons.delete'),
        'confirm' => __('buttons.confirm'),
        'cancel' => __('buttons.cancel'),
    ]) }}">

    @php
        $theme = $store->theme ?? null;
        $primaryColor = $theme?->primary_color ?? '#4f46e5';
        $secondaryColor = $theme?->secondary_color ?? '#7c3aed';
        $fontFamily = $theme?->font_family ?? 'Cairo';
        $pageTitle = $title ?? ($store->name ?? config('app.name'));
        $pageDesc = $store->description ?? '';

        $isPreview = request()->has('preview');
        $isOwner =
            auth()->check() &&
            ($store->user_id === auth()->id() ||
                $store
                    ->members()
                    ->where('user_id', auth()->id())
                    ->exists());
        $showPreviewBanner = $isPreview && $isOwner;
    @endphp
    <link rel="icon" href="{{ ($store->seo?->favicon) ? asset('storage/' . $store->seo->favicon) : asset('img/icons/store/favicon.ico') }}" type="image/x-icon" />
    <title>{{ $pageTitle }}</title>
    @if ($pageDesc)
        <meta name="description" content="{{ Str::limit(strip_tags($pageDesc), 160) }}">
    @endif

    <style>
        :root {
            --store-primary: {{ preg_replace('/[^a-fA-F0-9#]/', '', $primaryColor) }};
            --store-secondary: {{ preg_replace('/[^a-fA-F0-9#]/', '', $secondaryColor) }};
            --store-font: '{{ preg_replace("/[^a-zA-Z0-9_\\- ]/", "", $fontFamily) }}', sans-serif;
            color-scheme: light;
        }

        .dark {
            color-scheme: dark;
        }

        body {
            font-family: var(--store-font);
        }

        ion-icon {
            --ionicon-font-size: 1em;
            visibility: visible !important;
            opacity: 1 !important;
        }

        .store-btn-primary {
            background-color: var(--store-primary);
            color: var(--store-btn-text, #ffffff) !important;
        }

        .store-btn-primary:hover {
            filter: brightness(0.9);
        }

        .dark .store-btn-primary:hover {
            filter: brightness(1.2);
        }

        .store-text-primary {
            color: var(--store-primary);
        }

        .dark .store-text-primary {
            color: color-mix(in srgb, var(--store-primary) 55%, white);
        }

        .store-bg-primary {
            background-color: var(--store-primary);
        }

        .store-bg-primary-soft {
            background-color: color-mix(in srgb, var(--store-primary) 10%, transparent);
        }

        .dark .store-bg-primary-soft {
            background-color: color-mix(in srgb, var(--store-primary) 20%, transparent);
        }

        .store-border-primary {
            border-color: var(--store-primary);
        }

        .store-btn-secondary {
            background-color: var(--store-secondary);
        }

        .store-btn-secondary:hover {
            filter: brightness(0.9);
        }

        .dark .store-btn-secondary:hover {
            filter: brightness(1.2);
        }

        .store-text-secondary {
            color: var(--store-secondary);
        }

        .store-gradient {
            background: linear-gradient(135deg, var(--store-primary), var(--store-secondary));
        }

        .dark input:focus, .dark select:focus, .dark textarea:focus {
            --tw-ring-color: color-mix(in srgb, var(--store-primary) 35%, transparent) !important;
        }
    </style>

    {{-- Lazy-load the merchant-selected Google Font (Inter is already bundled via app.css) --}}
    @if ($fontUrl = \App\Support\Storefront\StorefrontSections::googleFontUrl($fontFamily))
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link rel="stylesheet" href="{{ $fontUrl }}">
    @endif

    <script>
        (function() {
            var c = getComputedStyle(document.documentElement).getPropertyValue('--store-primary').trim();
            if (!c) return;
            var m = c.replace('#', '').match(/.{2}/g);
            if (!m) return;
            var r = parseInt(m[0], 16) / 255,
                g = parseInt(m[1], 16) / 255,
                b = parseInt(m[2], 16) / 255;
            var lum = 0.299 * r + 0.587 * g + 0.114 * b;
            var text = lum > 0.55 ? '#000000' : '#ffffff';
            document.documentElement.style.setProperty('--store-btn-text', text);
        })();
    </script>

    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/storefront.js'])
    <script type="module" src="{{ asset('vendor/ionicons/ionicons.esm.js') }}"></script>
    <script nomodule src="{{ asset('vendor/ionicons/ionicons.js') }}"></script>
</head>

<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 antialiased min-h-screen flex flex-col">

    {{-- Preview Mode Banner --}}
    @if ($showPreviewBanner)
        <div
            class="store-gradient px-4 py-2.5 text-center text-sm font-medium flex items-center justify-center gap-2 shadow-md z-[60] text-white">
            <ion-icon name="eye-outline" class="text-lg"></ion-icon>
            {{ __('storefront.preview_mode') }}
            <a href="{{ route('storefront.home', ['store' => $store->slug]) }}"
                class="ml-3 inline-flex items-center gap-1 bg-white/20 hover:bg-white/30 rounded-lg px-3 py-1 text-xs font-semibold transition">
                <ion-icon name="open-outline" class="text-sm"></ion-icon>
                {{ __('storefront.view_live_store') }}
            </a>
        </div>
    @endif

    {{-- Header --}}
    <header class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="{{ route('storefront.home', ['store' => $store->slug]) }}" class="flex items-center gap-3 group">
                @if ($store->logo ?? null)
                    <img src="{{ asset('storage/' . $store->logo) }}" alt="{{ $store->name }}"
                        class="h-9 w-9 rounded-full object-cover group-hover:ring-2 group-hover:ring-offset-2 ring-offset-white dark:ring-offset-gray-800 store-border-primary transition">
                @else
                    <div
                        class="h-9 w-9 rounded-full store-bg-primary flex items-center justify-center text-white font-bold text-sm">
                        {{ strtoupper(substr($store->name, 0, 1)) }}
                    </div>
                @endif
                <span class="font-semibold text-lg text-gray-900 dark:text-white">{{ $store->name }}</span>
            </a>

            <div class="flex items-center gap-1">
                <x-storefront-lang-switcher />

                @if ($store->phone ?? null)
                    <a href="tel:{{ $store->phone }}"
                        class="text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition hidden sm:inline-flex items-center gap-1"
                        title="{{ __('storefront.call_us') }}">
                        <ion-icon name="call-outline" class="text-lg"></ion-icon>
                    </a>
                @endif

                <button
                    onclick="var html=document.documentElement;var isDark=html.classList.contains('dark');if(isDark){html.classList.remove('dark');localStorage.setItem('edz-theme','light');}else{html.classList.add('dark');localStorage.setItem('edz-theme','dark');}"
                    class="p-2.5 sm:p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 transition min-h-[44px] min-w-[44px] flex items-center justify-center"
                    aria-label="Toggle dark mode">
                    <svg class="hidden dark:block" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="5"></circle>
                        <line x1="12" y1="1" x2="12" y2="3"></line>
                        <line x1="12" y1="21" x2="12" y2="23"></line>
                        <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                        <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                        <line x1="1" y1="12" x2="3" y2="12"></line>
                        <line x1="21" y1="12" x2="23" y2="12"></line>
                        <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                        <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                    </svg>
                    <svg class="dark:hidden" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                    </svg>
                </button>

                {{-- Direct-order mode: single-product stores skip the cart
                     entirely; the template's "order now" goes straight to checkout. --}}
                @unless ($store->landing_template?->value === 'single_product')
                    @livewire('storefront.mini-cart')
                @endunless
            </div>
        </div>
    </header>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="bg-emerald-50 dark:bg-emerald-900/30 border-b border-emerald-200 dark:border-emerald-800"
            x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between">
                <p class="text-sm text-emerald-700 dark:text-emerald-300 flex items-center gap-2">
                    <ion-icon name="checkmark-circle-outline" class="text-lg"></ion-icon>
                    {{ session('success') }}
                </p>
                <button x-on:click="show = false"
                    class="text-emerald-500 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300"
                    aria-label="{{ __('general.close') }}"><ion-icon name="close-outline"
                        class="text-lg"></ion-icon></button>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-50 dark:bg-red-900/30 border-b border-red-200 dark:border-red-800" x-data="{ show: true }"
            x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between">
                <p class="text-sm text-red-700 dark:text-red-300 flex items-center gap-2">
                    <ion-icon name="alert-circle-outline" class="text-lg"></ion-icon>
                    {{ session('error') }}
                </p>
                <button x-on:click="show = false"
                    class="text-red-500 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300"
                    aria-label="{{ __('general.close') }}"><ion-icon name="close-outline"
                        class="text-lg"></ion-icon></button>
            </div>
        </div>
    @endif

    {{-- Main content --}}
    <main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8">
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    @if ($store->logo ?? null)
                        <img src="{{ asset('storage/' . $store->logo) }}" alt="{{ $store->name }}"
                            class="h-7 w-7 rounded-full object-cover">
                    @else
                        <div
                            class="h-7 w-7 rounded-full store-bg-primary flex items-center justify-center text-white font-bold text-xs">
                            {{ strtoupper(substr($store->name, 0, 1)) }}
                        </div>
                    @endif
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        &copy; {{ date('Y') }} {{ $store->name }} — {{ __('storefront.powered_by') }} <span
                            class="font-semibold store-text-primary">Edzeery</span>
                    </span>
                </div>
                <div class="flex items-center gap-4 text-sm text-gray-400 dark:text-gray-500">
                    @if ($store->phone ?? null)
                        <a href="tel:{{ $store->phone }}"
                            class="flex items-center gap-1 hover:text-gray-600 dark:hover:text-gray-300 transition">
                            <ion-icon name="call-outline"></ion-icon>
                            {{ $store->phone }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </footer>

    {{-- Notice Toast (limits / errors / confirmations) --}}
    <div x-data="edzNotice()" x-on:edz-notice.window="show($event.detail)" x-cloak
        class="fixed bottom-24 {{ isRTL() ? 'start-6' : 'end-6' }} z-[70] pointer-events-none">
        <div x-show="visible" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 scale-95"
            class="pointer-events-auto flex items-center gap-3 border rounded-2xl shadow-xl px-4 py-3 min-w-[260px] max-w-sm"
            :class="tone === 'success'
                ? 'bg-white dark:bg-gray-800 border-green-200 dark:border-green-800'
                : (tone === 'danger'
                    ? 'bg-white dark:bg-gray-800 border-red-200 dark:border-red-800'
                    : 'bg-white dark:bg-gray-800 border-amber-200 dark:border-amber-700')">
            <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center"
                :class="tone === 'success'
                    ? 'bg-green-100 dark:bg-green-900/30'
                    : (tone === 'danger' ? 'bg-red-100 dark:bg-red-900/30' : 'bg-amber-100 dark:bg-amber-900/30')">
                <ion-icon class="text-lg"
                    :class="tone === 'success'
                        ? 'text-green-600 dark:text-green-400'
                        : (tone === 'danger' ? 'text-red-600 dark:text-red-400' : 'text-amber-600 dark:text-amber-400')"
                    :name="tone === 'success' ? 'checkmark-outline' : (tone === 'danger' ? 'close-circle-outline' : 'alert-circle-outline')"></ion-icon>
            </div>
            <p class="min-w-0 text-sm font-semibold text-gray-900 dark:text-white" x-text="message"></p>
        </div>
    </div>

    {{-- Cart Toast --}}
    <div x-data="cartToast()" x-on:cart-updated.window="show()" x-cloak
        class="fixed bottom-6 {{ isRTL() ? 'start-6' : 'end-6' }} z-[70] pointer-events-none">        <div x-show="visible" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 scale-95"
            class="pointer-events-auto flex items-center gap-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-xl px-4 py-3 min-w-[240px]">
            <div
                class="flex-shrink-0 w-8 h-8 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                <ion-icon name="checkmark-outline" class="text-green-600 dark:text-green-400 text-lg"></ion-icon>
            </div>
            <div class="min-w-0">
                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('storefront.added_to_cart') }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('storefront.review_cart') }}</p>
            </div>
        </div>
    </div>

    @livewireScripts

    <script>
        // Bridge legacy session flashes into the edz-notice channel. Runs
        // before swal.js's DOMContentLoaded hook, which will find nothing to
        // consume — preventing duplicate toasts.
        (function () {
            var el = document.querySelector('[data-sw]');
            if (!el) return;
            var tone = el.dataset.sw === 'success' ? 'success' : 'danger';
            var title = el.dataset.swTitle || el.dataset.swMessage || '';
            el.remove();
            if (!title) return;
            window.dispatchEvent(new CustomEvent('edz-notice', {
                detail: {
                    title: title,
                    tone: tone
                }
            }));
        })();

        function cartToast() {
            return {
                visible: false,
                timeout: null,
                show() {
                    clearTimeout(this.timeout);
                    this.visible = true;
                    this.timeout = setTimeout(() => this.visible = false, 2500);
                }
            }
        }

        // Storefront notification channel: deterministic corner toasts fed by
        // the "edz-notice" browser event (limits, errors, confirmations).
        function edzNotice() {
            return {
                visible: false,
                message: '',
                tone: 'warning',
                timeout: null,
                show(detail) {
                    if (!detail || !detail.title) return;
                    this.message = detail.title;
                    this.tone = ['success', 'danger'].includes(detail.tone) ? detail.tone : 'warning';
                    clearTimeout(this.timeout);
                    this.visible = true;
                    this.timeout = setTimeout(() => this.visible = false, 4000);
                }
            }
        }

        function productGallery() {
            return {
                active: 0,
                lightbox: false,
                total: 0,
                init() {
                    const mainGallery = this.$refs.mainGallery;
                    this.total = mainGallery
                        ? mainGallery.querySelectorAll('img[x-show]').length
                        : this.$el.querySelectorAll('[x-show^="active ==="]').length;
                    let startX = 0;
                    this.$el.addEventListener('touchstart', e => {
                        startX = e.touches[0].clientX;
                    }, {
                        passive: true
                    });
                    this.$el.addEventListener('touchend', e => {
                        const diff = startX - e.changedTouches[0].clientX;
                        if (Math.abs(diff) > 50) {
                            diff > 0 ? this.next() : this.prev();
                        }
                    }, {
                        passive: true
                    });
                },
                next() {
                    this.active = (this.active + 1) % this.total;
                    this.scrollThumb();
                },
                prev() {
                    this.active = (this.active - 1 + this.total) % this.total;
                    this.scrollThumb();
                },
                goTo(i) {
                    this.active = i;
                    this.scrollThumb();
                },
                scrollThumb() {
                    const thumbs = this.$refs.thumbs;
                    if (!thumbs) return;
                    const btn = thumbs.children[this.active];
                    if (btn) btn.scrollIntoView({
                        behavior: 'smooth',
                        block: 'nearest',
                        inline: 'center'
                    });
                },
                openLightbox() {
                    if (this.total > 0) this.lightbox = true;
                }
            }
        }

        function productInfo(variants, lowStockTemplate, inStockLabel, outOfStockLabel, outOfStockShortLabel, addToCartLabel) {
            return {
                variants,
                lowStockTemplate,
                inStockLabel,
                outOfStockLabel,
                outOfStockShortLabel,
                addToCartLabel,
                get activeVariant() {
                    return this.variants[this.$wire.selectedVariantId] || Object.values(this.variants)[0] || null;
                },
                get stock() {
                    return this.activeVariant ? parseInt(this.activeVariant.stock) : 1;
                },
                get threshold() {
                    return this.activeVariant ? parseInt(this.activeVariant.threshold) : 0;
                },
                get maxQty() {
                    if (!this.activeVariant || this.activeVariant.cap === null || this.activeVariant.cap === undefined) return null;
                    return parseInt(this.activeVariant.cap);
                },
                get isOutOfStock() {
                    return !this.activeVariant || (this.maxQty !== null ? this.maxQty <= 0 : this.stock <= 0);
                },
                get stockDotClass() {
                    if (this.isOutOfStock) return 'bg-red-500';
                    if (this.stock <= this.threshold) return 'bg-amber-500';
                    return 'bg-green-500';
                },
                get stockTextClass() {
                    if (this.isOutOfStock) return 'text-red-600 dark:text-red-400';
                    if (this.stock <= this.threshold) return 'text-amber-600 dark:text-amber-400';
                    return 'text-green-600 dark:text-green-400';
                },
                get stockLabel() {
                    if (!this.activeVariant) return '';
                    if (this.isOutOfStock) return this.outOfStockLabel;
                    if (this.stock <= this.threshold) return this.lowStockTemplate.replace(':count', String(this.stock));
                    return this.inStockLabel;
                },
                select(id) {
                    this.$wire.set('selectedVariantId', id);
                    const v = this.variants[id];
                    if (!v) return;
                    const cap = v.cap === null || v.cap === undefined ? null : parseInt(v.cap);
                    const qty = parseInt(this.$wire.quantity);
                    if (cap !== null && cap > 0 && qty > cap) {
                        this.$wire.set('quantity', cap);
                    }
                }
            };
        }
    </script>

    @if (session('swal.type'))
        <div data-sw="{{ session('swal.type') }}" data-sw-title="{{ session('swal.title', '') }}"
            data-sw-message="{{ session('swal.message', '') }}" hidden></div>
    @endif

</body>

</html>
