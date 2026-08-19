<!DOCTYPE html>
<html lang="<?php echo e($lang ?? app()->getLocale()); ?>" dir="<?php echo e($dir ?? 'ltr'); ?>" class="h-full scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <?php
        $theme = $store->theme ?? null;
        $primaryColor = $theme?->primary_color ?? '#4f46e5';
        $secondaryColor = $theme?->secondary_color ?? '#7c3aed';
        $fontFamily = $theme?->font_family ?? 'Cairo';
        $pageTitle = $title ?? $store->name ?? config('app.name');
        $pageDesc = $store->description ?? '';

        $isPreview = request()->has('preview');
        $isOwner = auth()->check() && ($store->user_id === auth()->id() || $store->members()->where('user_id', auth()->id())->exists());
        $showPreviewBanner = $isPreview && $isOwner;
    ?>

    <title><?php echo e($pageTitle); ?></title>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pageDesc): ?>
        <meta name="description" content="<?php echo e(Str::limit(strip_tags($pageDesc), 160)); ?>">
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <style>
        :root {
            --store-primary: <?php echo e($primaryColor); ?>;
            --store-secondary: <?php echo e($secondaryColor); ?>;
            --store-font: '<?php echo e($fontFamily); ?>', sans-serif;
        }
        body { font-family: var(--store-font); }
        ion-icon { --ionicon-font-size: 1em; visibility: visible !important; opacity: 1 !important; }
        .store-btn-primary { background-color: var(--store-primary); }
        .store-btn-primary:hover { filter: brightness(0.9); }
        .store-text-primary { color: var(--store-primary); }
        .store-bg-primary { background-color: var(--store-primary); }
        .store-border-primary { border-color: var(--store-primary); }
        .store-btn-secondary { background-color: var(--store-secondary); }
        .store-btn-secondary:hover { filter: brightness(0.9); }
        .store-text-secondary { color: var(--store-secondary); }
        .store-gradient { background: linear-gradient(135deg, var(--store-primary), var(--store-secondary)); }
    </style>

    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/storefront.js']); ?>
    <script type="module" src="<?php echo e(asset('vendor/ionicons/ionicons.esm.js')); ?>"></script>
    <script nomodule src="<?php echo e(asset('vendor/ionicons/ionicons.js')); ?>"></script>
</head>

<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 antialiased min-h-screen flex flex-col">

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showPreviewBanner): ?>
        <div class="store-gradient px-4 py-2.5 text-center text-sm font-medium flex items-center justify-center gap-2 shadow-md z-[60] text-white">
            <ion-icon name="eye-outline" class="text-lg"></ion-icon>
            <?php echo e(__('storefront.preview_mode')); ?>

            <a href="<?php echo e(route('storefront.home', ['store' => $store->slug])); ?>" class="ml-3 inline-flex items-center gap-1 bg-white/20 hover:bg-white/30 rounded-lg px-3 py-1 text-xs font-semibold transition">
                <ion-icon name="open-outline" class="text-sm"></ion-icon>
                <?php echo e(__('storefront.view_live_store')); ?>

            </a>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <header class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="<?php echo e(route('storefront.home', ['store' => $store->slug])); ?>" class="flex items-center gap-3 group">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($store->logo ?? null): ?>
                    <img src="<?php echo e(asset('storage/' . $store->logo)); ?>" alt="<?php echo e($store->name); ?>" class="h-9 w-9 rounded-full object-cover group-hover:ring-2 group-hover:ring-offset-2 store-border-primary transition">
                <?php else: ?>
                    <div class="h-9 w-9 rounded-full store-bg-primary flex items-center justify-center text-white font-bold text-sm">
                        <?php echo e(strtoupper(substr($store->name, 0, 1))); ?>

                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <span class="font-semibold text-lg text-gray-900 dark:text-white"><?php echo e($store->name); ?></span>
            </a>

            <div class="flex items-center gap-1">
                <?php
                    $storeSettings = $store->settings ?? null;
                    $hasSupportedLangs = \Illuminate\Support\Facades\Schema::hasColumn('store_settings', 'supported_languages');
                    $supportedLangs = $hasSupportedLangs ? ($storeSettings?->supported_languages ?? []) : [];
                    $supportedLangs = array_filter($supportedLangs);
                    $currentLocale = app()->getLocale();
                ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($supportedLangs) > 1): ?>
                    <div x-data="{ open: false }" class="relative">
                        <button x-on:click="open = !open" x-on:click.outside="open = false"
                            class="text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition flex items-center gap-1 text-sm">
                            <ion-icon name="language-outline" class="text-lg"></ion-icon>
                            <span class="hidden sm:inline uppercase"><?php echo e($currentLocale); ?></span>
                            <ion-icon name="chevron-down-outline" class="text-xs"></ion-icon>
                        </button>
                        <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                            class="absolute right-0 mt-2 w-40 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 py-1 z-50">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $supportedLangs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lang): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <a href="<?php echo e(route('storefront.lang', ['store' => $store->slug, 'locale' => $lang])); ?>"
                                    class="flex items-center gap-2 px-4 py-2 text-sm transition
                                        <?php echo e($currentLocale === $lang ? 'store-text-primary font-semibold bg-gray-50 dark:bg-gray-700' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700'); ?>">
                                    <span class="w-5 text-center"><?php echo e(match($lang) { 'ar' => '🇸🇦', 'fr' => '🇫🇷', 'en' => '🇬🇧', 'es' => '🇪🇸', default => '🌐' }); ?></span>
                                    <?php echo e(__('storefront.lang_' . $lang)); ?>

                                </a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($store->phone ?? null): ?>
                    <a href="tel:<?php echo e($store->phone); ?>" class="text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition hidden sm:inline-flex items-center gap-1" title="<?php echo e(__('storefront.call_us')); ?>">
                        <ion-icon name="call-outline" class="text-lg"></ion-icon>
                    </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('storefront.mini-cart');

$__key = null;

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-2858451194-0', $__key);

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
        <div class="bg-emerald-50 dark:bg-emerald-900/30 border-b border-emerald-200 dark:border-emerald-800" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between">
                <p class="text-sm text-emerald-700 dark:text-emerald-300 flex items-center gap-2">
                    <ion-icon name="checkmark-circle-outline" class="text-lg"></ion-icon>
                    <?php echo e(session('success')); ?>

                </p>
                <button x-on:click="show = false" class="text-emerald-500 hover:text-emerald-700"><ion-icon name="close-outline" class="text-lg"></ion-icon></button>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
        <div class="bg-red-50 dark:bg-red-900/30 border-b border-red-200 dark:border-red-800" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between">
                <p class="text-sm text-red-700 dark:text-red-300 flex items-center gap-2">
                    <ion-icon name="alert-circle-outline" class="text-lg"></ion-icon>
                    <?php echo e(session('error')); ?>

                </p>
                <button x-on:click="show = false" class="text-red-500 hover:text-red-700"><ion-icon name="close-outline" class="text-lg"></ion-icon></button>
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
                        <img src="<?php echo e(asset('storage/' . $store->logo)); ?>" alt="<?php echo e($store->name); ?>" class="h-7 w-7 rounded-full object-cover">
                    <?php else: ?>
                        <div class="h-7 w-7 rounded-full store-bg-primary flex items-center justify-center text-white font-bold text-xs">
                            <?php echo e(strtoupper(substr($store->name, 0, 1))); ?>

                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        &copy; <?php echo e(date('Y')); ?> <?php echo e($store->name); ?> — <?php echo e(__('storefront.powered_by')); ?> <span class="font-semibold store-text-primary">Edzeery</span>
                    </span>
                </div>
                <div class="flex items-center gap-4 text-sm text-gray-400 dark:text-gray-500">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($store->phone ?? null): ?>
                        <a href="tel:<?php echo e($store->phone); ?>" class="flex items-center gap-1 hover:text-gray-600 dark:hover:text-gray-300 transition">
                            <ion-icon name="call-outline"></ion-icon>
                            <?php echo e($store->phone); ?>

                        </a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
    </footer>

    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>


    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('swal.type')): ?>
        <div data-sw="<?php echo e(session('swal.type')); ?>"
             data-sw-title="<?php echo e(session('swal.title', '')); ?>"
             data-sw-message="<?php echo e(session('swal.message', '')); ?>" hidden></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

</body>

</html>
<?php /**PATH C:\laragon\www\edzeery\resources\views/components/layouts/storefront.blade.php ENDPATH**/ ?>