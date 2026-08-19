<!DOCTYPE html>
<html lang="<?php echo e($lang); ?>" dir="<?php echo e($dir); ?>" class="h-full scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    
    <meta name="description" content="<?php echo e(__('landing.meta_description')); ?>">
    <meta name="keywords" content="ecommerce, stores, saas, payments, edzeery">
    <meta name="author" content="Edzeery">
    <meta property="og:title" content="<?php echo e(config('app.name')); ?>">
    <meta property="og:description" content="<?php echo e(__('landing.meta_description')); ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo e(url()->current()); ?>">
    <meta property="og:image" content="<?php echo e(asset('images/og.png')); ?>">
    <meta name="twitter:card" content="summary_large_image">

    <title><?php echo e(isset($title) ? config('app.name') . ' | ' . $title : config('app.name')); ?></title>

    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

</head>

<body
    class="min-h-screen flex flex-col items-center justify-center
            bg-surface-bg
              text-ink
             transition-colors duration-300 antialiased">

    
    <div class="fixed inset-0 pointer-events-none overflow-hidden -z-10">
        <div class="absolute -top-40 -right-40 w-96 h-96 rounded-full bg-brand-500/5 blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 rounded-full bg-accent-500/5 blur-3xl"></div>
    </div>

    
    <div class="absolute top-4
    <?php echo e($algin); ?>-4 flex items-center gap-2 z-50">
        
        <?php if (isset($component)) { $__componentOriginal0833810ddf751288ebafc69f0fa98b01 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0833810ddf751288ebafc69f0fa98b01 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.lang-switcher','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('lang-switcher'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0833810ddf751288ebafc69f0fa98b01)): ?>
<?php $attributes = $__attributesOriginal0833810ddf751288ebafc69f0fa98b01; ?>
<?php unset($__attributesOriginal0833810ddf751288ebafc69f0fa98b01); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0833810ddf751288ebafc69f0fa98b01)): ?>
<?php $component = $__componentOriginal0833810ddf751288ebafc69f0fa98b01; ?>
<?php unset($__componentOriginal0833810ddf751288ebafc69f0fa98b01); ?>
<?php endif; ?>

        
        <?php if (isset($component)) { $__componentOriginald69910f8a028c7647855f2296e768b41 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald69910f8a028c7647855f2296e768b41 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dark-toggle','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dark-toggle'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald69910f8a028c7647855f2296e768b41)): ?>
<?php $attributes = $__attributesOriginald69910f8a028c7647855f2296e768b41; ?>
<?php unset($__attributesOriginald69910f8a028c7647855f2296e768b41); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald69910f8a028c7647855f2296e768b41)): ?>
<?php $component = $__componentOriginald69910f8a028c7647855f2296e768b41; ?>
<?php unset($__componentOriginald69910f8a028c7647855f2296e768b41); ?>
<?php endif; ?>
    </div>

    <main class="w-full max-w-lg px-4 animate-fade-up">

        
        <div class="text-center my-6">
            <a href="<?php echo e(route('landing')); ?>" class="flex items-center justify-center gap-3">
                <?php if (isset($component)) { $__componentOriginal8892e718f3d0d7a916180885c6f012e7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8892e718f3d0d7a916180885c6f012e7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.application-logo','data' => ['class' => 'w-16 h-16 text-primary-600 dark:text-primary-400']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('application-logo'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-16 h-16 text-primary-600 dark:text-primary-400']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8892e718f3d0d7a916180885c6f012e7)): ?>
<?php $attributes = $__attributesOriginal8892e718f3d0d7a916180885c6f012e7; ?>
<?php unset($__attributesOriginal8892e718f3d0d7a916180885c6f012e7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8892e718f3d0d7a916180885c6f012e7)): ?>
<?php $component = $__componentOriginal8892e718f3d0d7a916180885c6f012e7; ?>
<?php unset($__componentOriginal8892e718f3d0d7a916180885c6f012e7); ?>
<?php endif; ?>
                <span class="font-bold text-2xl tracking-tight text-ink">
                    <?php echo e(config('app.name', 'Edzeery')); ?>

                </span>
            </a>
            <p class="text-sm text-ink-muted mt-2">
                <?php echo e(__('auth.platform_subtitle')); ?>

            </p>
        </div>

        
        <div class="w-full">
            <?php echo e($slot); ?>

        </div>

    </main>

</body>

</html>
<?php /**PATH C:\laragon\www\edzeery\resources\views/components/layouts/guest.blade.php ENDPATH**/ ?>