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
  <link rel="icon" href="<?php echo e(asset('img/icons/newlogo.ico')); ?>" type="image/x-icon" />
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js', 'resources/js/edz-loader.js', 'resources/js/panel.js']); ?>
    <script type="module" src="<?php echo e(asset('vendor/ionicons/ionicons.esm.js')); ?>"></script>

</head>

<body
    class="min-h-screen flex flex-col items-center justify-center
            bg-surface-bg
            text-ink
            transition-colors duration-300 antialiased">

    
    <div class="fixed inset-0 pointer-events-none overflow-hidden -z-10" aria-hidden="true">
        <div class="absolute -top-40 -right-40 w-96 h-96 rounded-full bg-brand-500/5 blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 rounded-full bg-brand-400/5 blur-3xl"></div>
        <div class="absolute top-1/3 left-1/4 w-64 h-64 rounded-full bg-brand-300/3 blur-2xl"></div>
    </div>

    
    <div class="absolute top-4 <?php echo e($algin); ?>-4 flex items-center gap-2 z-50">
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

    <main class="w-full max-w-6xl px-4 py-8 animate-fade-up">

        
        <div class="text-center mb-8">
            <a href="<?php echo e(route('landing')); ?>" class="inline-flex items-center justify-center gap-3 group">
                <?php if (isset($component)) { $__componentOriginal8892e718f3d0d7a916180885c6f012e7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8892e718f3d0d7a916180885c6f012e7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.application-logo','data' => ['class' => 'w-12 h-12 text-brand-600 dark:text-brand-400 transition-transform duration-300 group-hover:scale-110']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('application-logo'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-12 h-12 text-brand-600 dark:text-brand-400 transition-transform duration-300 group-hover:scale-110']); ?>
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
                <span class="font-bold text-xl tracking-tight text-ink">
                    <?php echo e(config('app.name', 'Edzeery')); ?>

                </span>
            </a>
        </div>

        
        <div class="w-full">
            <?php echo e($slot); ?>

        </div>

    </main>

    
    <?php if (isset($component)) { $__componentOriginaleb2c0285848843393eb73e790b3190b1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaleb2c0285848843393eb73e790b3190b1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.global-loader','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.global-loader'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaleb2c0285848843393eb73e790b3190b1)): ?>
<?php $attributes = $__attributesOriginaleb2c0285848843393eb73e790b3190b1; ?>
<?php unset($__attributesOriginaleb2c0285848843393eb73e790b3190b1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaleb2c0285848843393eb73e790b3190b1)): ?>
<?php $component = $__componentOriginaleb2c0285848843393eb73e790b3190b1; ?>
<?php unset($__componentOriginaleb2c0285848843393eb73e790b3190b1); ?>
<?php endif; ?>

    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

</body>

</html>
<?php /**PATH C:\laragon\www\edzeery\resources\views\components\layouts\guest.blade.php ENDPATH**/ ?>