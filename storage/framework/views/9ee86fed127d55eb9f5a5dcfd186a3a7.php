<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'options' => [],
    'optionValue' => 'value',
    'optionLabel' => 'label',
    'optionHint' => null,
    'optionCode' => null,
    'optionExtra' => null,
    'placeholder' => null,
    'search' => false,
    'searchPlaceholder' => null,
    'disabled' => false,
    'error' => null,
    'icon' => null,
    'class' => '',
    'name' => null,
    'role' => null,
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
    'placeholder' => null,
    'search' => false,
    'searchPlaceholder' => null,
    'disabled' => false,
    'error' => null,
    'icon' => null,
    'class' => '',
    'name' => null,
    'role' => null,
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
    $uid = 'sf-select-' . \Illuminate\Support\Str::random(8);

    $jsOptions = collect($options)
        ->map(function ($item, $key) use ($optionValue, $optionLabel, $optionHint, $optionCode, $optionExtra) {
            // Options may be Eloquent models, stdClass, or plain arrays: read
            // the value/label/hint through data_get so x-text never receives an
            // object (which Alpine would render as "[object Object]").
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
                return [
                    'value' => (string) ($value ?? $key),
                    'label' => (string) ($label ?? $value ?? $key),
                    'hint' => $hint !== null ? (string) $hint : null,
                    'code' => $code !== null && $code !== '' ? (string) $code : null,
                    'extra' => $extra->map(fn($v) => (string) $v)->all(),
                ];
            }
            return [
                'value' => (string) $key,
                'label' => (string) $item,
                'hint' => null,
                'code' => null,
                'extra' => [],
            ];
        })
        ->values()
        ->all();

    $optionsAttr = htmlspecialchars(json_encode($jsOptions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8');
    $searchable = $search;

    // A round-trip happens for every pick when the select is lazy-loaded or
    // bound with a live/change modifier: gate + spinner until the server
    // acknowledges, so a rapid second tap never fires a racing request.
    $relativeAttrs = $attributes->getAttributes();
    $isLiveModel = collect(array_keys($relativeAttrs))
        ->map(fn ($k) => is_string($k) ? $k : '')
        ->first(fn ($k) => str_starts_with($k, 'wire:model') && str_contains($k, '.live')) !== null;
    $roundtrip = (bool) $lazy || $isLiveModel || $attributes->has('wire:change');

    $modelName = $attributes->whereStartsWith('wire:model.')->first()
        ?: $attributes->whereStartsWith('wire:model')->first();
?>

<div <?php echo e($attributes->merge(['class' => "sf-select relative $class"])->whereDoesntStartWith('wire:model')); ?>

    <?php if($role): ?> data-role="<?php echo e($role); ?>" <?php endif; ?>
    <?php if($lazy): ?> data-lazy="1" <?php endif; ?>
    data-source="<?php echo e($source); ?>" data-scope="<?php echo e($scope); ?>"
    <?php if($roundtrip): ?> data-roundtrip="1" <?php endif; ?>
    data-options="<?php echo $optionsAttr; ?>"
    x-data="storefrontSelect({
        options: <?php echo \Illuminate\Support\Js::from($jsOptions)->toHtml() ?>,
        searchable: <?php echo \Illuminate\Support\Js::from($searchable)->toHtml() ?>,
        modelName: <?php echo \Illuminate\Support\Js::from($modelName)->toHtml() ?>,
        placeholderLabel: <?php echo \Illuminate\Support\Js::from($placeholder)->toHtml() ?>,
        lazy: <?php echo \Illuminate\Support\Js::from((bool) $lazy)->toHtml() ?>,
        roundtrip: <?php echo \Illuminate\Support\Js::from($roundtrip)->toHtml() ?>,
        remoteSource: <?php echo \Illuminate\Support\Js::from($source)->toHtml() ?>,
        remoteScope: <?php echo \Illuminate\Support\Js::from($scope)->toHtml() ?>
    })" x-init="init()">

    <input type="hidden" <?php if($name): ?> name="<?php echo e($name); ?>" <?php endif; ?>
        x-model="selected" x-ref="hiddenInput" <?php echo e($attributes->whereStartsWith('wire:model')); ?>>

    <button type="button" x-ref="trigger" role="combobox" aria-haspopup="listbox"
        :aria-expanded="open.toString()" aria-controls="<?php echo e($uid); ?>-listbox"
        @click.prevent="toggle()"
        @keydown.arrow-down.prevent="moveHighlight(1)"
        @keydown.arrow-up.prevent="moveHighlight(-1)"
        @keydown.enter.prevent="open ? selectHighlighted() : toggle()"
        @keydown.escape="close()"
        <?php if($disabled): ?> disabled <?php endif; ?>
        :disabled="loading || <?php echo \Illuminate\Support\Js::from($disabled)->toHtml() ?>"
        class="sf-select__trigger w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700/50 text-gray-900 dark:text-white text-sm shadow-sm flex items-center gap-2 text-start transition-all duration-200 disabled:opacity-60 disabled:cursor-not-allowed"
        :style="open ? 'border-color: var(--store-primary); box-shadow: 0 0 0 3px color-mix(in srgb, var(--store-primary) 20%, transparent);' : ''"
        :class="{ 'sf-select__trigger--disabled': loading || <?php echo \Illuminate\Support\Js::from($disabled)->toHtml() ?> }">

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($icon): ?>
            <span class="text-gray-400 dark:text-gray-500 shrink-0">
                <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => $icon,'class' => 'w-4 h-4 text-base']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($icon),'class' => 'w-4 h-4 text-base']); ?>
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
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <span class="flex-1 min-w-0 text-start">
            <template x-if="currentLabel !== null">
                <span class="inline-flex items-center gap-1.5 min-w-0">
                    <template x-if="currentCode !== null">
                        <span class="shrink-0 inline-flex items-center justify-center min-w-6 px-1.5 py-0.5 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-300 text-[11px] font-semibold leading-none tabular-nums" x-text="currentCode"></span>
                    </template>
                    <span class="truncate">
                        <span x-text="currentLabel"></span>
                        <template x-if="currentHint !== null">
                            <span class="text-xs text-gray-400 dark:text-gray-500" x-text="' \u00b7 ' + currentHint"></span>
                        </template>
                    </span>
                </span>
            </template>
            <template x-if="currentLabel === null">
                <span class="text-gray-400 dark:text-gray-500 truncate" x-text="placeholderLabel || '\u2014'"></span>
            </template>
        </span>

        <template x-if="loading">
            <svg class="w-4 h-4 shrink-0 animate-spin text-gray-400 dark:text-gray-500" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
            </svg>
        </template>
        <template x-if="!loading">
            <svg class="w-4 h-4 text-gray-400 dark:text-gray-500 shrink-0 transition-transform duration-200" :class="{ 'rotate-180': open }" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
        </template>
    </button>

    <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
        :style="panelStyle"
        class="sf-select__panel bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl p-2 max-h-80 overflow-hidden flex flex-col origin-top">

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($search): ?>
            <div class="flex items-center gap-2 px-2 pb-2 mb-1 border-b border-gray-100 dark:border-gray-700">
                <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'magnifying-glass','class' => 'w-4 h-4 text-base text-gray-400 dark:text-gray-500 shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'magnifying-glass','class' => 'w-4 h-4 text-base text-gray-400 dark:text-gray-500 shrink-0']); ?>
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
                <input type="text" x-model="query" @input.debounce.150ms="highlighted = filteredOptions.length > 0 ? 0 : -1" @click.stop
                    class="flex-1 min-w-0 bg-transparent text-sm text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:outline-none"
                    :placeholder="$el.getAttribute('data-ph')" x-ref="searchInput" data-ph="<?php echo e($searchPlaceholder ?? __('storefront.search')); ?>"
                    @keydown.arrow-down.prevent="moveHighlight(1)"
                    @keydown.arrow-up.prevent="moveHighlight(-1)"
                    @keydown.enter.prevent="selectHighlighted()"
                    @keydown.escape="close()">
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <ul id="<?php echo e($uid); ?>-listbox" role="listbox" class="overflow-y-auto overflow-x-hidden overscroll-contain flex-1 sf-scroll">
            <template x-for="(opt, idx) in filteredOptions" :key="opt.value">
                <li role="option"
                    :class="{
                        'sf-select__option--selected': opt.value === selected
                    }"
                    :aria-selected="(opt.value === selected).toString()"
                    @click="select(opt.value)"
                    @mouseenter="highlighted = idx"
                    class="sf-select__option flex items-center gap-2.5 px-2.5 py-2 rounded-lg cursor-pointer transition-colors duration-75 text-sm text-gray-900 dark:text-white"
                    :class="highlighted === idx ? 'bg-gray-100 dark:bg-gray-700' : ''">
                    <span class="flex items-center justify-center w-4 shrink-0 text-gray-400 dark:text-gray-500" x-show="opt.value === selected">
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
                    <span class="flex flex-col min-w-0 flex-1">
                        <span class="inline-flex items-center gap-1.5 min-w-0">
                            <template x-if="opt.code">
                                <span class="shrink-0 inline-flex items-center justify-center min-w-6 px-1.5 py-0.5 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-300 text-[11px] font-semibold leading-none tabular-nums" x-text="opt.code"></span>
                            </template>
                            <span class="sf-select__option-label truncate" :class="{ 'font-semibold': opt.value === selected }" x-text="opt.label"></span>
                        </span>
                        <template x-if="opt.hint">
                            <span class="text-xs text-gray-400 dark:text-gray-500 truncate" x-text="opt.hint"></span>
                        </template>
                        <template x-for="(ln, i) in opt.extra" :key="i">
                            <span class="text-xs text-gray-400 dark:text-gray-500 truncate" x-text="ln"></span>
                        </template>
                    </span>
                </li>
            </template>

            <li x-show="loading" class="flex items-center justify-center gap-2 px-3 py-3 text-sm text-gray-400 dark:text-gray-500">
                <svg class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
                <span><?php echo e(__('messages.loading')); ?></span>
            </li>

            <li x-show="!loading && filteredOptions.length === 0" class="px-3 py-3 text-center text-sm text-gray-400 dark:text-gray-500">
                <?php echo e(__('storefront.no_options_found')); ?>

            </li>
        </ul>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($error): ?>
        <span class="text-xs font-medium text-red-500 dark:text-red-400 mt-1 block"><?php echo e($error); ?></span>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views\components\storefront\select.blade.php ENDPATH**/ ?>