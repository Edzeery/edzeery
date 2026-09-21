<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'options' => [],
    'optionValue' => 'value',
    'optionLabel' => 'label',
    'optionHint' => null,
    'optionCode' => null,
    'placeholder' => '—',
    'search' => false,
    'searchPlaceholder' => null,
    'searchMinChars' => 2,
    'size' => 'md',
    'disabled' => false,
    'error' => null,
    'icon' => null,
    'class' => '',
    'name' => null,
    'selected' => [],
    'maxVisibleChips' => 3,
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
    'options' => [],
    'optionValue' => 'value',
    'optionLabel' => 'label',
    'optionHint' => null,
    'optionCode' => null,
    'placeholder' => '—',
    'search' => false,
    'searchPlaceholder' => null,
    'searchMinChars' => 2,
    'size' => 'md',
    'disabled' => false,
    'error' => null,
    'icon' => null,
    'class' => '',
    'name' => null,
    'selected' => [],
    'maxVisibleChips' => 3,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $uid = 'edz-multi-select-' . \Illuminate\Support\Str::random(8);

    $jsOptions = collect($options)
        ->map(function ($item, $key) use ($optionValue, $optionLabel, $optionHint, $optionCode) {
            if (is_array($item) || is_object($item)) {
                $value = data_get($item, $optionValue);
                $label = data_get($item, $optionLabel);
                $hint = $optionHint ? data_get($item, $optionHint) : null;
                $code = $optionCode ? data_get($item, $optionCode) : null;
                return [
                    'value' => (string) ($value ?? $key),
                    'label' => (string) ($label ?? $value ?? $key),
                    'hint' => $hint !== null && $hint !== '' ? (string) $hint : null,
                    'code' => $code !== null && $code !== '' ? (string) $code : null,
                ];
            }
            return [
                'value' => (string) $key,
                'label' => (string) $item,
                'hint' => null,
                'code' => null,
            ];
        })
        ->values()
        ->all();

    $selectedValues = array_map('strval', array_values($selected));

    $sizeClass = match ($size) {
        'sm' => 'edz-select--sm',
        'lg' => 'edz-select--lg',
        default => '',
    };

    $errorClass = $error ? 'edz-select--error' : '';

    // Bound Livewire model path (options.0.values, ...). wire:model.* variants
    // (.live/.defer/...) must be matched before the plain wire:model so a
    // modifier twin never shadows the base binding.
    $modelName = $attributes->whereStartsWith('wire:model.')->first()
        ?: $attributes->whereStartsWith('wire:model')->first();
    $hasWireChange = (bool) $attributes->get('wire:change');
?>

<div <?php echo e($attributes->merge(['class' => "edz-select edz-multi-select $sizeClass $errorClass $class"])->whereDoesntStartWith('wire:model')->whereDoesntStartWith('wire:change')->whereDoesntStartWith('x-model')); ?>

    x-data="edzMultiSelect({
        searchable: <?php echo \Illuminate\Support\Js::from((bool) $search)->toHtml() ?>,
        searchMinChars: <?php echo \Illuminate\Support\Js::from($searchMinChars)->toHtml() ?>,
        modelName: <?php echo \Illuminate\Support\Js::from($modelName)->toHtml() ?>,
        roundtrip: <?php echo \Illuminate\Support\Js::from($hasWireChange)->toHtml() ?>,
        maxChips: <?php echo \Illuminate\Support\Js::from($maxVisibleChips)->toHtml() ?>
    })" x-init="init()">

    
    <select <?php if($name): ?> name="<?php echo e($name); ?>" <?php endif; ?>
        multiple hidden
        x-ref="hiddenInput"
        <?php echo e($attributes->whereStartsWith('wire:model')); ?>

        <?php echo e($attributes->whereStartsWith('wire:change')); ?>>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $jsOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($opt['value']); ?>"
                <?php if($opt['hint'] !== null): ?> data-hint="<?php echo e($opt['hint']); ?>" <?php endif; ?>
                <?php if($opt['code'] !== null): ?> data-code="<?php echo e($opt['code']); ?>" <?php endif; ?>
                <?php if(in_array($opt['value'], $selectedValues, true)): echo 'selected'; endif; ?>>
                <?php echo e($opt['label']); ?>

            </option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </select>

    
    <button type="button" x-ref="trigger" class="edz-select__trigger edz-multi-select__trigger"
        :class="{ 'edz-select__trigger--disabled': <?php echo \Illuminate\Support\Js::from($disabled)->toHtml() ?>, 'edz-select__trigger--open': open }"
        :disabled="<?php echo \Illuminate\Support\Js::from($disabled)->toHtml() ?>"
        <?php if($disabled): ?> disabled <?php endif; ?>
        role="combobox" aria-haspopup="listbox"
        :aria-expanded="open.toString()" aria-controls="<?php echo e($uid); ?>-listbox"
        @click.prevent="toggle()"
        @keydown.arrow-down.prevent="moveHighlight(1)"
        @keydown.arrow-up.prevent="moveHighlight(-1)"
        @keydown.enter.prevent="open ? selectHighlighted() : toggle()"
        @keydown.escape="close()">

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($icon): ?>
            <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => $icon,'class' => 'edz-select__icon w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($icon),'class' => 'edz-select__icon w-4 h-4']); ?>
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

        <span class="edz-select__text edz-multi-select__text">
            <template x-if="selectedOptions.length > 0">
                <span class="edz-multi-select__chips">
                    <template x-for="opt in visibleChips" :key="opt.value">
                        <span class="edz-multi-select__chip" @click.stop="removeValue(opt.value)"
                            :title="opt.label">
                            <span class="edz-multi-select__chip-label" x-text="opt.label"></span>
                            <span class="edz-multi-select__chip-remove">
                                <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'x-mark','class' => 'w-3 h-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'x-mark','class' => 'w-3 h-3']); ?>
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
                            </span>
                        </span>
                    </template>
                    <template x-if="moreCount > 0">
                        <span class="edz-multi-select__chip edz-multi-select__chip--more">
                            <span x-text="'+' + moreCount"></span>
                        </span>
                    </template>
                </span>
            </template>
            <template x-if="selectedOptions.length === 0">
                <span class="edz-select__placeholder"><?php echo e($placeholder); ?></span>
            </template>
        </span>

        <span class="edz-select__chevron-wrap" :class="{ 'rotate-180': open }">
            <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'chevron-down','class' => 'edz-select__chevron w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'chevron-down','class' => 'edz-select__chevron w-4 h-4']); ?>
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
        </span>
    </button>

    
    <div x-show="open" x-cloak
        x-transition:enter="edz-select-enter"
        x-transition:enter-start="edz-select-enter-start"
        x-transition:enter-end="edz-select-enter-end"
        x-transition:leave="edz-select-leave"
        x-transition:leave-start="edz-select-leave-start"
        x-transition:leave-end="edz-select-leave-end"
        :style="panelStyle"
        class="edz-select__panel edz-multi-select__panel">

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($search): ?>
            <div class="edz-select__search">
                <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'magnifying-glass','class' => 'edz-select__search-icon w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'magnifying-glass','class' => 'edz-select__search-icon w-4 h-4']); ?>
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
                <input type="text" x-model="query" @input.debounce.200ms="onQueryChange()" @click.stop
                    class="edz-select__search-input"
                    placeholder="<?php echo e($searchPlaceholder ?? __('merchant_panel.search')); ?>"
                    x-ref="searchInput"
                    @keydown.arrow-down.prevent="moveHighlight(1)"
                    @keydown.arrow-up.prevent="moveHighlight(-1)"
                    @keydown.enter.prevent="selectHighlighted()"
                    @keydown.escape="close()">
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <ul id="<?php echo e($uid); ?>-listbox" role="listbox" aria-multiselectable="true" class="edz-select__list">
            <template x-for="(opt, idx) in filteredOptions" :key="opt.value">
                <li role="option" class="edz-select__option edz-multi-select__option"
                    :class="{ 'edz-select__option--highlighted': highlighted === idx }"
                    :aria-selected="isSelected(opt.value).toString()"
                    @click.prevent="toggleValue(opt.value)"
                    @mouseenter="highlighted = idx">
                    <span class="edz-multi-select__check"
                        :class="{ 'edz-multi-select__check--on': isSelected(opt.value) }">
                        <template x-if="isSelected(opt.value)">
                            <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'check','class' => 'w-3 h-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'check','class' => 'w-3 h-3']); ?>
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
                        </template>
                    </span>
                    <span class="edz-select__option-content">
                        <span class="edz-select__option-line">
                            <template x-if="opt.code">
                                <span class="edz-code-badge" x-text="opt.code"></span>
                            </template>
                            <span class="edz-select__option-label" x-text="opt.label"></span>
                        </span>
                        <template x-if="opt.hint">
                            <span class="edz-select__option-hint" x-text="opt.hint"></span>
                        </template>
                    </span>
                </li>
            </template>

            <li x-show="filteredOptions.length === 0" class="edz-select__empty">
                <?php echo e(__('merchant_panel.no_options_found')); ?>

            </li>
        </ul>

        <div class="edz-multi-select__footer">
            <span class="edz-multi-select__count" x-text="selectedOptions.length"></span>
            <button type="button" class="edz-btn edz-btn--primary edz-btn--sm" @click="close()">
                <?php echo e(__('buttons.done')); ?>

            </button>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($error): ?>
        <span class="edz-select__error"><?php echo e($error); ?></span>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views/components/edz/multi-select.blade.php ENDPATH**/ ?>