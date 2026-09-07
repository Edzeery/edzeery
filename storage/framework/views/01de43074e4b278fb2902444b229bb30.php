<div wire:key="ie-<?php echo e($brand->id); ?>">
    <?php if (isset($component)) { $__componentOriginalf9bce473674ba88372f13b52b040213f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf9bce473674ba88372f13b52b040213f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.inline-edit-field','data' => ['field' => 'brand.name','value' => $brand->name,'editing' => ($editingField ?? null) === 'brand.name','error' => $editingError ?? null,'wire:start' => 'startEditName(' . $brand->id . ')','wire:save' => 'saveName','wire:cancel' => 'cancelName','wire:model' => 'editingValue']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.inline-edit-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['field' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('brand.name'),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($brand->name),'editing' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($editingField ?? null) === 'brand.name'),'error' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($editingError ?? null),'wire:start' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('startEditName(' . $brand->id . ')'),'wire:save' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('saveName'),'wire:cancel' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('cancelName'),'wire:model' => 'editingValue']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf9bce473674ba88372f13b52b040213f)): ?>
<?php $attributes = $__attributesOriginalf9bce473674ba88372f13b52b040213f; ?>
<?php unset($__attributesOriginalf9bce473674ba88372f13b52b040213f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf9bce473674ba88372f13b52b040213f)): ?>
<?php $component = $__componentOriginalf9bce473674ba88372f13b52b040213f; ?>
<?php unset($__componentOriginalf9bce473674ba88372f13b52b040213f); ?>
<?php endif; ?>
</div>
<?php /**PATH C:\laragon\www\edzeery\resources\views\tests-support\inline-edit-component.blade.php ENDPATH**/ ?>