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

<body class="bg-surface-bg
text-ink
antialiased">

    
    <?php if (isset($component)) { $__componentOriginala9b3e504c250665e0a13110a708b1bba = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala9b3e504c250665e0a13110a708b1bba = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.navbar','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.navbar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala9b3e504c250665e0a13110a708b1bba)): ?>
<?php $attributes = $__attributesOriginala9b3e504c250665e0a13110a708b1bba; ?>
<?php unset($__attributesOriginala9b3e504c250665e0a13110a708b1bba); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala9b3e504c250665e0a13110a708b1bba)): ?>
<?php $component = $__componentOriginala9b3e504c250665e0a13110a708b1bba; ?>
<?php unset($__componentOriginala9b3e504c250665e0a13110a708b1bba); ?>
<?php endif; ?>

    
    <main class="pt-24">
        <?php echo e($slot); ?>

    </main>

    
    <?php if (isset($component)) { $__componentOriginal2851f1e47c9108aacbab05e6d2ec4a68 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2851f1e47c9108aacbab05e6d2ec4a68 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.footer','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.footer'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2851f1e47c9108aacbab05e6d2ec4a68)): ?>
<?php $attributes = $__attributesOriginal2851f1e47c9108aacbab05e6d2ec4a68; ?>
<?php unset($__attributesOriginal2851f1e47c9108aacbab05e6d2ec4a68); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2851f1e47c9108aacbab05e6d2ec4a68)): ?>
<?php $component = $__componentOriginal2851f1e47c9108aacbab05e6d2ec4a68; ?>
<?php unset($__componentOriginal2851f1e47c9108aacbab05e6d2ec4a68); ?>
<?php endif; ?>

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
<?php /**PATH C:\laragon\www\edzeery\resources\views/components/layouts/landing-layout.blade.php ENDPATH**/ ?>