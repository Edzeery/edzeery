<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['templates', 'selectedId' => 1]));

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

foreach (array_filter((['templates', 'selectedId' => 1]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="bg-white rounded-lg shadow-sm p-6" x-data="{ selectedTemplate: <?php echo e($selectedId); ?> }">
    <h3 class="font-semibold text-gray-900 mb-4">Choose Template</h3>

    <div class="grid grid-cols-2 gap-4">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $templates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $template): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <button
                type="button"
                wire:click="$set('data.template_id', <?php echo e($template->id); ?>)"
                x-on:click="selectedTemplate = <?php echo e($template->id); ?>"
                class="relative group cursor-pointer rounded-lg overflow-hidden border-2 transition-all duration-200"
                x-bind:class="selectedTemplate === <?php echo e($template->id); ?>

                    ? 'border-blue-600 shadow-lg'
                    : 'border-gray-200 hover:border-gray-300'"
            >
                
                <div class="bg-linear-to-br from-gray-50 to-gray-100 flex items-center justify-center">
                    <div class="text-center p-4">
                        <div class="text-2xl mb-2">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php switch($template->slug):
                                case ('modern-minimalist'): ?>
                                    📄
                                    <?php break; ?>
                                <?php case ('classic-business'): ?>
                                    📋
                                    <?php break; ?>
                                <?php case ('creative-agency'): ?>
                                    🎨
                                    <?php break; ?>
                                <?php default: ?>
                                    📃
                            <?php endswitch; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <div class="text-xs font-semibold text-gray-600">
                            <?php echo e($template->name); ?>

                        </div>
                    </div>
                </div>

                
                <div
                    x-show="selectedTemplate === <?php echo e($template->id); ?>"
                    x-transition
                    class="absolute top-2 right-2 bg-blue-600 text-white rounded-full p-1"
                >
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </button>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <p class="text-xs text-gray-500 mt-4 text-center">
        Click a template to preview
    </p>
</div>
<?php /**PATH C:\laragon\www\edzeery\resources\views\components\invoices\template-selector.blade.php ENDPATH**/ ?>