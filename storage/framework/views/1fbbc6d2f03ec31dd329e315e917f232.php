<!DOCTYPE html>
<html lang="<?php echo e($lang ?? app()->getLocale()); ?>" dir="<?php echo e($dir ?? 'ltr'); ?>" class="h-full scroll-smooth">

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
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <meta name="swal-i18n" content="<?php echo e(json_encode([
        'confirm_delete_title' => __('messages.action_confirm'),
        'confirm_delete' => __('messages.action_confirm_delete'),
        'confirm_delete_named' => __('messages.action_confirm_delete') . ' "{name}"?',
        'confirm_bulk_delete' => __('messages.ask_delete'),
        'delete' => __('buttons.delete'),
        'confirm' => __('buttons.confirm'),
        'cancel' => __('buttons.cancel'),
    ])); ?>">

    <?php
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
    ?>
    <link rel="icon" href="<?php echo e(($store->seo?->favicon) ? asset('storage/' . $store->seo->favicon) : asset('img/icons/store/favicon.ico')); ?>" type="image/x-icon" />
    <title><?php echo e($pageTitle); ?></title>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pageDesc): ?>
        <meta name="description" content="<?php echo e(Str::limit(strip_tags($pageDesc), 160)); ?>">
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <style>
        :root {
            --store-primary: <?php echo e(preg_replace('/[^a-fA-F0-9#]/', '', $primaryColor)); ?>;
            --store-secondary: <?php echo e(preg_replace('/[^a-fA-F0-9#]/', '', $secondaryColor)); ?>;
            --store-font: '<?php echo e(preg_replace("/[^a-zA-Z0-9_\\- ]/", "", $fontFamily)); ?>', sans-serif;
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

    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/storefront.js']); ?>
    <script type="module" src="<?php echo e(asset('vendor/ionicons/ionicons.esm.js')); ?>"></script>
    <script nomodule src="<?php echo e(asset('vendor/ionicons/ionicons.js')); ?>"></script>
</head>

<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 antialiased min-h-screen flex flex-col">

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showPreviewBanner): ?>
        <div
            class="store-gradient px-4 py-2.5 text-center text-sm font-medium flex items-center justify-center gap-2 shadow-md z-[60] text-white">
            <ion-icon name="eye-outline" class="text-lg"></ion-icon>
            <?php echo e(__('storefront.preview_mode')); ?>

            <a href="<?php echo e(route('storefront.home', ['store' => $store->slug])); ?>"
                class="ml-3 inline-flex items-center gap-1 bg-white/20 hover:bg-white/30 rounded-lg px-3 py-1 text-xs font-semibold transition">
                <ion-icon name="open-outline" class="text-sm"></ion-icon>
                <?php echo e(__('storefront.view_live_store')); ?>

            </a>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <header class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="<?php echo e(route('storefront.home', ['store' => $store->slug])); ?>" class="flex items-center gap-3 group">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($store->logo ?? null): ?>
                    <img src="<?php echo e(asset('storage/' . $store->logo)); ?>" alt="<?php echo e($store->name); ?>"
                        class="h-9 w-9 rounded-full object-cover group-hover:ring-2 group-hover:ring-offset-2 ring-offset-white dark:ring-offset-gray-800 store-border-primary transition">
                <?php else: ?>
                    <div
                        class="h-9 w-9 rounded-full store-bg-primary flex items-center justify-center text-white font-bold text-sm">
                        <?php echo e(strtoupper(substr($store->name, 0, 1))); ?>

                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <span class="font-semibold text-lg text-gray-900 dark:text-white"><?php echo e($store->name); ?></span>
            </a>

            <div class="flex items-center gap-1">
                <?php if (isset($component)) { $__componentOriginal7b463796bd1d7d53e1902b7f1145ade8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7b463796bd1d7d53e1902b7f1145ade8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.storefront-lang-switcher','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('storefront-lang-switcher'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7b463796bd1d7d53e1902b7f1145ade8)): ?>
<?php $attributes = $__attributesOriginal7b463796bd1d7d53e1902b7f1145ade8; ?>
<?php unset($__attributesOriginal7b463796bd1d7d53e1902b7f1145ade8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7b463796bd1d7d53e1902b7f1145ade8)): ?>
<?php $component = $__componentOriginal7b463796bd1d7d53e1902b7f1145ade8; ?>
<?php unset($__componentOriginal7b463796bd1d7d53e1902b7f1145ade8); ?>
<?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($store->phone ?? null): ?>
                    <a href="tel:<?php echo e($store->phone); ?>"
                        class="text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition hidden sm:inline-flex items-center gap-1"
                        title="<?php echo e(__('storefront.call_us')); ?>">
                        <ion-icon name="call-outline" class="text-lg"></ion-icon>
                    </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

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

                <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('storefront.mini-cart');

$__key = null;

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-4088925592-0', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key);

echo $__html;

unset($__html);
unset($__key);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
            </div>
        </div>
    </header>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div class="bg-emerald-50 dark:bg-emerald-900/30 border-b border-emerald-200 dark:border-emerald-800"
            x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between">
                <p class="text-sm text-emerald-700 dark:text-emerald-300 flex items-center gap-2">
                    <ion-icon name="checkmark-circle-outline" class="text-lg"></ion-icon>
                    <?php echo e(session('success')); ?>

                </p>
                <button x-on:click="show = false"
                    class="text-emerald-500 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300"
                    aria-label="<?php echo e(__('general.close')); ?>"><ion-icon name="close-outline"
                        class="text-lg"></ion-icon></button>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
        <div class="bg-red-50 dark:bg-red-900/30 border-b border-red-200 dark:border-red-800" x-data="{ show: true }"
            x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between">
                <p class="text-sm text-red-700 dark:text-red-300 flex items-center gap-2">
                    <ion-icon name="alert-circle-outline" class="text-lg"></ion-icon>
                    <?php echo e(session('error')); ?>

                </p>
                <button x-on:click="show = false"
                    class="text-red-500 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300"
                    aria-label="<?php echo e(__('general.close')); ?>"><ion-icon name="close-outline"
                        class="text-lg"></ion-icon></button>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8">
        <?php echo e($slot); ?>

    </main>

    
    <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($store->logo ?? null): ?>
                        <img src="<?php echo e(asset('storage/' . $store->logo)); ?>" alt="<?php echo e($store->name); ?>"
                            class="h-7 w-7 rounded-full object-cover">
                    <?php else: ?>
                        <div
                            class="h-7 w-7 rounded-full store-bg-primary flex items-center justify-center text-white font-bold text-xs">
                            <?php echo e(strtoupper(substr($store->name, 0, 1))); ?>

                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        &copy; <?php echo e(date('Y')); ?> <?php echo e($store->name); ?> — <?php echo e(__('storefront.powered_by')); ?> <span
                            class="font-semibold store-text-primary">Edzeery</span>
                    </span>
                </div>
                <div class="flex items-center gap-4 text-sm text-gray-400 dark:text-gray-500">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($store->phone ?? null): ?>
                        <a href="tel:<?php echo e($store->phone); ?>"
                            class="flex items-center gap-1 hover:text-gray-600 dark:hover:text-gray-300 transition">
                            <ion-icon name="call-outline"></ion-icon>
                            <?php echo e($store->phone); ?>

                        </a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
    </footer>

    
    <div x-data="cartToast()" x-on:cart-updated.window="show()" x-cloak
        class="fixed bottom-6 <?php echo e(isRTL() ? 'start-6' : 'end-6'); ?> z-[70] pointer-events-none">
        <div x-show="visible" x-transition:enter="transition ease-out duration-300"
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
                <p class="text-sm font-semibold text-gray-900 dark:text-white"><?php echo e(__('storefront.added_to_cart')); ?>

                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400"><?php echo e(__('storefront.review_cart')); ?></p>
            </div>
        </div>
    </div>

    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>


    <script>
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

        function productGallery() {
            return {
                active: 0,
                lightbox: false,
                total: 0,
                init() {
                    this.total = this.$el.querySelectorAll('[x-show^="active ==="]').length;
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
    </script>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('swal.type')): ?>
        <div data-sw="<?php echo e(session('swal.type')); ?>" data-sw-title="<?php echo e(session('swal.title', '')); ?>"
            data-sw-message="<?php echo e(session('swal.message', '')); ?>" hidden></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

</body>

</html>
<?php /**PATH C:\laragon\www\edzeery\resources\views\components\layouts\storefront.blade.php ENDPATH**/ ?>