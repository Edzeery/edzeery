<?php if (isset($component)) { $__componentOriginalcbdb9614ce9918f9093053258b644089 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcbdb9614ce9918f9093053258b644089 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.landing-layout','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.landing-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php echo $__env->make('landing.sections.hero', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('landing.sections.social-proof', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('landing.sections.services', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('landing.sections.how-it-works', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('landing.sections.plans', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('landing.sections.payments', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('landing.sections.faq', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('landing.sections.final-cta', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcbdb9614ce9918f9093053258b644089)): ?>
<?php $attributes = $__attributesOriginalcbdb9614ce9918f9093053258b644089; ?>
<?php unset($__attributesOriginalcbdb9614ce9918f9093053258b644089); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcbdb9614ce9918f9093053258b644089)): ?>
<?php $component = $__componentOriginalcbdb9614ce9918f9093053258b644089; ?>
<?php unset($__componentOriginalcbdb9614ce9918f9093053258b644089); ?>
<?php endif; ?>
<?php /**PATH C:\laragon\www\edzeery\resources\views\landing\index.blade.php ENDPATH**/ ?>