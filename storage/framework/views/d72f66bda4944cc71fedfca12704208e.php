<!DOCTYPE html>
<html lang="<?php echo e($lang); ?>" dir="<?php echo e($dir); ?>" x-data="{ dark: localStorage.getItem('darkMode') === 'true' }" x-init="if (dark) {
    document.documentElement.classList.add('dark')
}
$watch('dark', val => {
    localStorage.setItem('darkMode', val)
    val
        ?
        document.documentElement.classList.add('dark') :
        document.documentElement.classList.remove('dark')
})"
    :class="{ 'dark': dark }" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title>
        <?php echo e(isset($title) ? config('app.name', 'Edzeery') . ' | ' . $title : config('app.name', 'Edzeery')); ?>

    </title>

    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</head>

<body
    class="
        min-h-screen
        bg-surface-bg
        text-ink
        antialiased
        transition-colors duration-300
    ">

    
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

    
    <main class="pt-20 min-h-[calc(100vh-5rem)]">
        <?php echo e($slot ?? ''); ?>

        <?php echo $__env->yieldContent('content'); ?>
        
        <?php if (isset($component)) { $__componentOriginal2851f1e47c9108aacbab05e6d2ec4a68 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2851f1e47c9108aacbab05e6d2ec4a68 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.footer','data' => ['class' => 'mt-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.footer'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mt-0']); ?>
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
    </main>
</body>

</html>
<?php /**PATH C:\laragon\www\edzeery\resources\views/components/layouts/app.blade.php ENDPATH**/ ?>