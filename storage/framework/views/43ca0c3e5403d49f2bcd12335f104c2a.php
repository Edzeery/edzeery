<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'isOpen' => false,
    'showCloseButton' => true,
    'size' => 'md',
    'preventClose' => false,
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
    'isOpen' => false,
    'showCloseButton' => true,
    'size' => 'md',
    'preventClose' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $sizeClass = "edz-modal__panel--{$size}";
?>

<div x-data='{
    open: <?php echo \Illuminate\Support\Js::from($isOpen)->toHtml() ?>,
    preventClose: <?php echo \Illuminate\Support\Js::from($preventClose)->toHtml() ?>,
    depth: 0,
    init() {
        const stack = window.__edzModalStack ||= [];
        if (this.open) {
            if (!stack.includes(this.$el)) stack.push(this.$el);
            this.depth = stack.indexOf(this.$el) + 1;
            document.body.style.overflow = "hidden";
        }
        this.$watch("open", (value) => {
            const stack = window.__edzModalStack ||= [];
            if (value) {
                if (!stack.includes(this.$el)) stack.push(this.$el);
                this.depth = stack.indexOf(this.$el) + 1;
                document.body.style.overflow = "hidden";
            } else {
                const i = stack.indexOf(this.$el);
                if (i !== -1) stack.splice(i, 1);
                this.depth = 0;
                document.body.style.overflow = stack.length ? "hidden" : "unset";
                this.$dispatch("edz-modal-closed");
            }
        });
    }
}' x-show="open" x-cloak
    @keydown.escape.window="if (!preventClose) { const s = window.__edzModalStack ||= []; if (s[s.length - 1] === $el) open = false }"
    @edz-modal-closed="if ($event.target !== $event.currentTarget) $event.stopPropagation()"
    :style="'--edz-modal-depth:' + depth"
    class="edz-modal"
    <?php echo e($attributes->except('class')); ?>>

    <!-- Backdrop -->
    <div @click="if (!preventClose) open = false" class="edz-modal__backdrop"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
    </div>

    <!-- Panel -->
    <div @click.stop class="edz-modal__panel <?php echo e($sizeClass); ?> <?php echo e($attributes->get('class')); ?> "
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95">

        <!-- Close Button -->
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showCloseButton): ?>
            <button type="button" @click="open = false" class="edz-modal__close end-4 me-auto" aria-label="Close">
                <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'x-mark','class' => 'w-5 h-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'x-mark','class' => 'w-5 h-5']); ?>
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
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <!-- Modal Body -->
        <div>
            <?php echo e($slot); ?>

        </div>
    </div>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views/components/edz/modal.blade.php ENDPATH**/ ?>