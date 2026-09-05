<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'editing' => false,
    'value' => null,
    'empty' => '—',
    'type' => 'text',
    'rows' => 3,
    'placeholder' => null,
    'id' => null,
    'error' => null,
    'icon' => null,
    'class' => '',
    'wire:start',
    'wire:save',
    'wire:cancel',
    'wire:model',
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'editing' => false,
    'value' => null,
    'empty' => '—',
    'type' => 'text',
    'rows' => 3,
    'placeholder' => null,
    'id' => null,
    'error' => null,
    'icon' => null,
    'class' => '',
    'wire:start',
    'wire:save',
    'wire:cancel',
    'wire:model',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $uid = $id ?? 'edz-inline-edit-' . \Illuminate\Support\Str::random(8);
    $saveMethod = $attributes->get('wire:save');
    $startMethod = $attributes->get('wire:start');
    $cancelMethod = $attributes->get('wire:cancel');
    $hasError = $editing && filled($error);
?>

<div <?php echo e($attributes->merge(['class' => "edz-inline-edit $class"])->whereDoesntStartWith('wire:')); ?>>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editing): ?>
        
        <div class="edz-inline-edit__edit" wire:key="<?php echo e($uid); ?>-edit"
            x-data x-init="requestAnimationFrame(() => $refs.input?.focus())">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($type === 'textarea'): ?>
                <textarea rows="<?php echo e($rows); ?>" x-ref="input" class="edz-inline-edit__input <?php if($hasError): ?> edz-inline-edit__input--error <?php endif; ?>"
                    id="<?php echo e($uid); ?>" placeholder="<?php echo e($placeholder); ?>"
                    <?php echo e($attributes->whereStartsWith('wire:model')); ?>

                    x-on:keydown.escape.window="$wire.<?php echo e($cancelMethod); ?>()"></textarea>
            <?php else: ?>
                <input type="<?php echo e($type); ?>" x-ref="input" class="edz-inline-edit__input <?php if($hasError): ?> edz-inline-edit__input--error <?php endif; ?>"
                    id="<?php echo e($uid); ?>" placeholder="<?php echo e($placeholder); ?>"
                    <?php echo e($attributes->whereStartsWith('wire:model')); ?>

                    x-on:keydown.escape="$wire.<?php echo e($cancelMethod); ?>()">
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="edz-inline-edit__actions">
                <button type="button" class="edz-inline-edit__save"
                    <?php echo e($attributes->whereStartsWith('wire:save')); ?> wire:loading.attr="disabled">
                    <?php if (isset($component)) { $__componentOriginalf4c9959d3f2732b60b7f028a5155a98c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf4c9959d3f2732b60b7f028a5155a98c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.spinner','data' => ['wire:target' => ''.e($saveMethod).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.spinner'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:target' => ''.e($saveMethod).'']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf4c9959d3f2732b60b7f028a5155a98c)): ?>
<?php $attributes = $__attributesOriginalf4c9959d3f2732b60b7f028a5155a98c; ?>
<?php unset($__attributesOriginalf4c9959d3f2732b60b7f028a5155a98c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf4c9959d3f2732b60b7f028a5155a98c)): ?>
<?php $component = $__componentOriginalf4c9959d3f2732b60b7f028a5155a98c; ?>
<?php unset($__componentOriginalf4c9959d3f2732b60b7f028a5155a98c); ?>
<?php endif; ?>
                    <span wire:loading.remove wire:target="<?php echo e($saveMethod); ?>">Save</span>
                </button>
                <button type="button" class="edz-inline-edit__cancel" <?php echo e($attributes->whereStartsWith('wire:cancel')); ?>>
                    Cancel
                </button>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasError): ?>
                <p class="edz-inline-edit__error"><?php echo e($error); ?></p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    <?php else: ?>
        
        <button type="button" class="edz-inline-edit__display"
            <?php if($startMethod): ?> wire:click="<?php echo e($startMethod); ?>" <?php endif; ?>>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($icon): ?>
                <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => $icon,'class' => 'edz-inline-edit__icon w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($icon),'class' => 'edz-inline-edit__icon w-4 h-4']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $attributes = $__attributesOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $component = $__componentOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__componentOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(is_null($value) || $value === ''): ?>
                <span class="edz-inline-edit__empty"><?php echo e($empty); ?></span>
            <?php else: ?>
                <span class="edz-inline-edit__value"><?php echo e($value); ?></span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </button>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\laragon\www\edzeery\resources\views\components\edz\inline-edit-field.blade.php ENDPATH**/ ?>