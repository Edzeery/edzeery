<!DOCTYPE html>

<html lang="<?php echo e($lang); ?>" dir="<?php echo e($dir); ?>" class="h-full scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo e($title ?? 'Dashboard'); ?> | <?php echo e(config('app.name')); ?></title>
    <link rel="icon" href="<?php echo e(asset('img/icons/newlogo.ico')); ?>" type="image/x-icon" />
    <!-- Legacy TailAdmin -->
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>

    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</head>

<body
    class=" bg-surface-bg text-ink
        antialiased transition-colors duration-300"
    x-data="{ 'loaded': true }" x-init="$store.sidebar.isExpanded = window.innerWidth >= 1280;
    const checkMobile = () => {
        if (window.innerWidth < 1280) {
            $store.sidebar.setMobileOpen(false);
            $store.sidebar.isExpanded = false;
        } else {
            $store.sidebar.isMobileOpen = false;
            $store.sidebar.isExpanded = true;
        }
    };
    window.addEventListener('resize', checkMobile);">

    
    <?php if (isset($component)) { $__componentOriginalb61632ad80e39a3770bbaf55089af949 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb61632ad80e39a3770bbaf55089af949 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.common.preloader','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('common.preloader'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb61632ad80e39a3770bbaf55089af949)): ?>
<?php $attributes = $__attributesOriginalb61632ad80e39a3770bbaf55089af949; ?>
<?php unset($__attributesOriginalb61632ad80e39a3770bbaf55089af949); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb61632ad80e39a3770bbaf55089af949)): ?>
<?php $component = $__componentOriginalb61632ad80e39a3770bbaf55089af949; ?>
<?php unset($__componentOriginalb61632ad80e39a3770bbaf55089af949); ?>
<?php endif; ?>
    

    <div class="min-h-screen xl:flex">
        <?php echo $__env->make('layouts.backdrop', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('layouts.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="flex-1 transition-all duration-300 ease-in-out"
            :class="{
                '<?php echo e($isRtl ? 'xl:mr-[290px]' : 'xl:ml-[290px]'); ?>': $store.sidebar.isExpanded || $store.sidebar
                    .isHovered,
                '<?php echo e($isRtl ? 'xl:mr-[90px]' : 'xl:ml-[90px]'); ?>': !$store.sidebar.isExpanded && !$store.sidebar
                    .isHovered,
                'ml-0 mr-0': $store.sidebar.isMobileOpen
            }">
            <!-- app header start -->
            <?php echo $__env->make('layouts.app-header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <!-- app header end -->
            <div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
                <?php echo $__env->yieldContent('content'); ?>
            </div>
        </div>

    </div>

</body>

<?php echo $__env->yieldPushContent('scripts'); ?>

</html>
<?php /**PATH C:\laragon\www\edzeery\resources\views\layouts\app.blade.php ENDPATH**/ ?>