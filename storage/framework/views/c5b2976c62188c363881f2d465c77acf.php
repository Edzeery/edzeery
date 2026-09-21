<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'label' => '',
    'side' => 'top',
    'maxWidth' => '288px',
    'block' => false,
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
    'label' => '',
    'side' => 'top',
    'maxWidth' => '288px',
    'block' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($label === ''): ?>
    <?php echo e($slot); ?>

<?php else: ?>
    <div
        <?php echo e($attributes->class([
            'edz-tooltip',
            'edz-tooltip--block' => $block,
        ])); ?>

        style="--edz-tooltip-max-w: <?php echo e($maxWidth); ?>;"
        x-data="edzTooltip(<?php echo \Illuminate\Support\Js::from($side)->toHtml() ?>)"
        @mouseenter="enter()"
        @mouseleave="leave()"
        @focusin="enter()"
        @focusout="leave()"
        @click.capture="hide()"
        @scroll.window.passive="hide()"
    >
        <span x-ref="trigger" class="edz-tooltip__trigger <?php echo e($block ? 'edz-tooltip__trigger--block' : ''); ?>">
            <?php echo e($slot); ?>

        </span>

        <span
            x-ref="bubble"
            x-show="visible"
            x-cloak
            role="tooltip"
            x-transition:enter="edz-tooltip-enter"
            x-transition:enter-start="edz-tooltip-enter-start"
            x-transition:enter-end="edz-tooltip-enter-end"
            x-transition:leave="edz-tooltip-leave"
            x-transition:leave-start="edz-tooltip-leave-start"
            x-transition:leave-end="edz-tooltip-leave-end"
            :class="{ 'edz-tooltip__bubble--positioning': positioning }"
            :style="bubbleStyle"
            class="edz-tooltip__bubble"
        >
            <?php echo e($label); ?>

        </span>
    </div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?><?php /**PATH C:\laragon\www\edzeery\resources\views\components\edz\tooltip.blade.php ENDPATH**/ ?>