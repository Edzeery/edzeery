<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'options' => [],
    'optionValue' => 'value',
    'optionLabel' => 'label',
    'optionHint' => null,
    'optionCode' => null,
    'optionExtra' => null,
    'optionDivider' => null,
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
    'value' => null,
    'lazy' => false,
    'source' => null,
    'scope' => null,
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
    'optionExtra' => null,
    'optionDivider' => null,
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
    'value' => null,
    'lazy' => false,
    'source' => null,
    'scope' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $uid = 'edz-select-' . \Illuminate\Support\Str::random(8);

    $jsOptions = collect($options)
        ->map(function ($item, $key) use ($optionValue, $optionLabel, $optionHint, $optionCode, $optionExtra, $optionDivider) {
            if (is_array($item) || is_object($item)) {
                $value = data_get($item, $optionValue);
                $label = data_get($item, $optionLabel);
                $hint = $optionHint ? data_get($item, $optionHint) : null;
                $code = $optionCode ? data_get($item, $optionCode) : null;
                $extra = $optionExtra
                    ? collect(data_get($item, $optionExtra, []))
                        ->filter(fn($v) => filled($v))
                        ->values()
                    : collect();
                $isDivider = $optionDivider ? (bool) data_get($item, $optionDivider) : false;
                return [
                    'value' => (string) ($value ?? $key),
                    'label' => (string) ($label ?? $value ?? $key),
                    'hint' => $hint !== null ? (string) $hint : null,
                    'code' => $code !== null && $code !== '' ? (string) $code : null,
                    'extra' => $extra->map(fn($v) => (string) $v)->all(),
                    'isDivider' => $isDivider,
                ];
            }
            return [
                'value' => (string) $key,
                'label' => (string) $item,
                'hint' => null,
                'code' => null,
                'extra' => [],
                'isDivider' => false,
            ];
        })
        ->values()
        ->all();

    // Livewire morphs the trigger/opens in place; Alpine reads this attribute
    // whenever it changes so the option list stays in sync with the server.
    $optionsAttr = htmlspecialchars(json_encode($jsOptions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8');

    $sizeClass = match ($size) {
        'sm' => 'edz-select--sm',
        'lg' => 'edz-select--lg',
        default => '',
    };

    $errorClass = $error ? 'edz-select--error' : '';
    $searchable = $search;
    $hasBackendSearch = $attributes->has('wire:search');

    // A round-trip happens for every pick when the select is lazy-loaded or
    // bound with a live/change modifier: gate + spinner until the server
    // acknowledges, so a rapid second tap never fires a racing request.
    $relativeAttrs = $attributes->getAttributes();
    $isLiveModel = collect(array_keys($relativeAttrs))
        ->map(fn ($k) => is_string($k) ? $k : '')
        ->first(fn ($k) => str_starts_with($k, 'wire:model') && str_contains($k, '.live')) !== null;
    $roundtrip = (bool) $lazy || $isLiveModel || $attributes->has('wire:change');

    // Bound Livewire model path (form.shipping_provider_id, product_id, ...).
    // wire:model.* variants (.live/.defer/...) must be matched before the
    // plain wire:model so a modifier twin never shadows the base binding.
    $modelName = $attributes->whereStartsWith('wire:model.')->first()
        ?: $attributes->whereStartsWith('wire:model')->first();
?>

<div <?php echo e($attributes->merge(['class' => "edz-select $sizeClass $errorClass $class"])->whereDoesntStartWith('wire:model')->whereDoesntStartWith('x-model')->whereDoesntStartWith('wire:search')); ?>

    data-options="<?php echo e($optionsAttr); ?>"
    <?php if($lazy): ?> data-lazy="1" <?php endif; ?>
    data-source="<?php echo e($source); ?>" data-scope="<?php echo e($scope); ?>"
    <?php if($roundtrip): ?> data-roundtrip="1" <?php endif; ?>
    x-data="edzSelect({
        options: <?php echo \Illuminate\Support\Js::from($jsOptions)->toHtml() ?>,
        searchable: <?php echo \Illuminate\Support\Js::from($searchable)->toHtml() ?>,
        hasBackendSearch: <?php echo \Illuminate\Support\Js::from($hasBackendSearch)->toHtml() ?>,
        searchMinChars: <?php echo \Illuminate\Support\Js::from($searchMinChars)->toHtml() ?>,
        wireMethodName: <?php echo \Illuminate\Support\Js::from($attributes->get('wire:search'))->toHtml() ?>,
        modelName: <?php echo \Illuminate\Support\Js::from($modelName)->toHtml() ?>,
        lazy: <?php echo \Illuminate\Support\Js::from((bool) $lazy)->toHtml() ?>,
        roundtrip: <?php echo \Illuminate\Support\Js::from($roundtrip)->toHtml() ?>,
        remoteSource: <?php echo \Illuminate\Support\Js::from($source)->toHtml() ?>,
        remoteScope: <?php echo \Illuminate\Support\Js::from($scope)->toHtml() ?>,
        loadingLabel: <?php echo \Illuminate\Support\Js::from(__('messages.loading'))->toHtml() ?>
    })" x-init="init()">

    <input type="hidden" <?php if($name): ?> name="<?php echo e($name); ?>" <?php endif; ?>
        <?php if($value !== null && $value !== ''): ?> value="<?php echo e($value); ?>" <?php endif; ?>
        x-model="selected" x-ref="hiddenInput" <?php echo e($attributes->whereStartsWith('wire:model')); ?>>

    <button type="button" x-ref="trigger" class="edz-select__trigger"
        :class="{ 'edz-select__trigger--disabled': loading || <?php echo \Illuminate\Support\Js::from($disabled)->toHtml() ?>, 'edz-select__trigger--open': open }"
        :disabled="loading || <?php echo \Illuminate\Support\Js::from($disabled)->toHtml() ?>"
        <?php if($disabled): ?> disabled <?php endif; ?> role="combobox" aria-haspopup="listbox"
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

        <span class="edz-select__text">
            <template x-if="currentLabel !== null">
                <span class="edz-select__text-inner">
                    <template x-if="currentCode !== null">
                        <span class="edz-code-badge" x-text="currentCode"></span>
                    </template>
                    <span class="edz-select__text-label">
                        <span x-text="currentLabel"></span>
                        <template x-if="currentHint !== null">
                            <span class="edz-select__hint" x-text="' \u2014 ' + currentHint"></span>
                        </template>
                    </span>
                </span>
            </template>
            <template x-if="currentLabel === null">
                <span class="edz-select__placeholder"><?php echo e($placeholder); ?></span>
            </template>
        </span>

        <span class="edz-select__chevron-wrap" :class="{ 'rotate-180': open }">
            <template x-if="loading">
                <svg class="edz-spinner" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83" />
                </svg>
            </template>
            <template x-if="!loading">
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
            </template>
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
        class="edz-select__panel">

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

        <ul id="<?php echo e($uid); ?>-listbox" role="listbox" class="edz-select__list">
            <template x-for="(opt, idx) in filteredOptions" :key="opt.value">
                <li role="option" class="edz-select__option"
                    :class="{
                        'edz-select__divider': opt.isDivider,
                        'edz-select__option--highlighted': highlighted === idx && !opt.isDivider,
                        'edz-select__option--selected': opt.value === selected && !opt.isDivider
                    }"
                    :aria-selected="(!opt.isDivider && opt.value === selected).toString()"
                    @click="!opt.isDivider && select(opt.value)"
                    @mouseenter="if (!opt.isDivider) highlighted = idx">
                    <span class="edz-select__option-check" x-show="!opt.isDivider && opt.value === selected">
                        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'check','class' => 'w-3.5 h-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'check','class' => 'w-3.5 h-3.5']); ?>
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
                    <span class="edz-select__option-content">
                        <span class="edz-select__option-line">
                            <template x-if="opt.isDivider">
                                <span class="text-[10px] font-semibold text-ink-muted uppercase tracking-wide" x-text="opt.label"></span>
                            </template>
                            <template x-if="!opt.isDivider && opt.code">
                                <span class="edz-code-badge" x-text="opt.code"></span>
                            </template>
                            <template x-if="!opt.isDivider">
                                <span class="edz-select__option-label" x-text="opt.label"></span>
                            </template>
                        </span>
                        <template x-if="!opt.isDivider && opt.hint">
                            <span class="edz-select__option-hint" x-text="opt.hint"></span>
                        </template>
                        <template x-for="(ln, i) in opt.extra" :key="i">
                            <span class="edz-select__option-hint" x-text="ln"></span>
                        </template>
                    </span>
                </li>
            </template>

            <li x-show="loading" class="edz-select__loading">
                <svg class="edz-spinner" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83" />
                </svg>
                <span x-text="labelForType"></span>
            </li>

            <li x-show="!loading && filteredOptions.length === 0" class="edz-select__empty">
                <?php echo e(__('merchant_panel.no_options_found')); ?>

            </li>
        </ul>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($error): ?>
        <span class="edz-select__error"><?php echo e($error); ?></span>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>


<?php /**PATH C:\laragon\www\edzeery\resources\views\components\edz\select.blade.php ENDPATH**/ ?>