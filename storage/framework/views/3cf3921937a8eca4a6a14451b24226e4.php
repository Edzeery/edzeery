<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title' => '',
    'icon' => 'ellipsis-horizontal',
    'closeExpr' => 'close()',
    'smWidth' => 'sm:w-56',
    'smMaxHeight' => 'sm:max-h-64',
    'smPad' => 'sm:p-1.5 sm:pb-1.5',
    'smZ' => 'sm:z-[200]',
    'keepHeaderSm' => false,
    'scrimFade' => false,
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
    'title' => '',
    'icon' => 'ellipsis-horizontal',
    'closeExpr' => 'close()',
    'smWidth' => 'sm:w-56',
    'smMaxHeight' => 'sm:max-h-64',
    'smPad' => 'sm:p-1.5 sm:pb-1.5',
    'smZ' => 'sm:z-[200]',
    'keepHeaderSm' => false,
    'scrimFade' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>




<div x-show="open" x-cloak <?php if($scrimFade): ?> x-transition.opacity <?php endif; ?>
    @click="<?php echo e($closeExpr); ?>"
    class="fixed inset-0 z-[205] bg-black/40 backdrop-blur-sm sm:hidden"></div>


<div x-show="open" x-cloak
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 translate-y-3"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-3"
    :style="menuStyle"
    class="<?php echo \Illuminate\Support\Arr::toCssClasses([
        'fixed inset-x-0 bottom-0 sm:inset-x-auto sm:bottom-auto z-[210] w-full rounded-t-2xl sm:rounded-xl',
        'border border-b-0 sm:border-b border-surface-border bg-surface',
        'p-3 pb-[calc(1rem+env(safe-area-inset-bottom))]',
        'shadow-[0_-16px_48px_-12px_rgba(15,23,42,.25)] sm:shadow-lg',
        'max-h-[70vh] overflow-y-auto edz-scroll',
        $smZ,
        $smWidth,
        $smPad,
        $smMaxHeight,
    ]); ?>">
    <span
        class="pointer-events-none mx-auto mb-2 block h-1 w-10 rounded-full bg-surface-border sm:hidden"></span>
    <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
        'flex items-center justify-between gap-2 px-1 mb-1.5',
        $keepHeaderSm ? '' : 'sm:hidden',
    ]); ?>">
        <p class="inline-flex items-center gap-1.5 text-xs font-semibold text-ink uppercase tracking-wide">
            <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => $icon,'class' => 'w-3.5 h-3.5 text-ink-muted']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($icon),'class' => 'w-3.5 h-3.5 text-ink-muted']); ?>
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
            <span><?php echo e($title); ?></span>
        </p>
        <button @click="<?php echo e($closeExpr); ?>" type="button"
            class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                '-m-1 p-1 rounded-lg text-ink-muted hover:text-ink hover:bg-surface-tertiary',
                $keepHeaderSm ? 'sm:hidden' : '',
            ]); ?>"
            title="<?php echo e(__('general.close')); ?>">
            <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'x-mark','class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'x-mark','class' => 'w-4 h-4']); ?>
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
        </button>
    </div>
    <?php echo e($slot); ?>

</div><?php /**PATH C:\laragon\www\edzeery\resources\views\components\edz\mobile-bottom-sheet.blade.php ENDPATH**/ ?>