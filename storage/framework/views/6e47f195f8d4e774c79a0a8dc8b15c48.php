<!DOCTYPE html>
<html lang="<?php echo e($lang ?? app()->getLocale()); ?>" dir="<?php echo e($dir ?? 'ltr'); ?>" class="h-full scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo e($title ?? $store->name ?? config('app.name')); ?></title>

    <?php
        $theme = $store->theme ?? null;
        $primaryColor = $theme?->primary_color ?? '#4f46e5';
        $secondaryColor = $theme?->secondary_color ?? '#7c3aed';
        $fontFamily = $theme?->font_family ?? 'Cairo';
        $sections = $theme?->homepage_sections ?? ['hero', 'social_proof', 'faq', 'cta'];
    ?>

    <style>
        :root {
            --store-primary: <?php echo e($primaryColor); ?>;
            --store-secondary: <?php echo e($secondaryColor); ?>;
            --store-font: '<?php echo e($fontFamily); ?>', sans-serif;
        }
        body { font-family: var(--store-font); }
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

    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</head>

<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 antialiased min-h-screen flex flex-col">

    
    <header class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="/" class="flex items-center gap-3">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($store->logo ?? null): ?>
                    <img src="<?php echo e(asset('storage/' . $store->logo)); ?>" alt="<?php echo e($store->name); ?>" class="h-9 w-9 rounded-full object-cover">
                <?php else: ?>
                    <div class="h-9 w-9 rounded-full store-bg-primary flex items-center justify-center text-white font-bold text-sm">
                        <?php echo e(strtoupper(substr($store->name, 0, 1))); ?>

                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <span class="font-semibold text-lg text-gray-900 dark:text-white"><?php echo e($store->name); ?></span>
            </a>

            <div class="flex items-center gap-4">
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

    
    <main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8">
        <?php echo e($slot); ?>

    </main>

    
    <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 py-6 mt-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-sm text-gray-500 dark:text-gray-400">
            &copy; <?php echo e(date('Y')); ?> <?php echo e($store->name); ?> — <?php echo e(__('Powered by')); ?> Edzeery
        </div>
    </footer>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('swal.type')): ?>
        <div data-sw="<?php echo e(session('swal.type')); ?>"
             data-sw-title="<?php echo e(session('swal.title', '')); ?>"
             data-sw-message="<?php echo e(session('swal.message', '')); ?>" hidden></div>
    <?php elseif(session('success')): ?>
        <div data-sw="success" data-sw-message="<?php echo e(session('success')); ?>" hidden></div>
    <?php elseif(session('error')): ?>
        <div data-sw="error" data-sw-message="<?php echo e(session('error')); ?>" hidden></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

</body>

</html>
<?php /**PATH C:\laragon\www\edzeery\resources\views/components/layouts/storefront.blade.php ENDPATH**/ ?>