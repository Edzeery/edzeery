<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'options' => [],
    'optionValue' => 'value',
    'optionLabel' => 'label',
    'optionHint' => null,
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
        ->map(function ($item, $key) use ($optionValue, $optionLabel, $optionHint) {
            if (is_array($item)) {
                return [
                    'value' => data_get($item, $optionValue, $item),
                    'label' => data_get($item, $optionLabel, $item),
                    'hint' => $optionHint ? data_get($item, $optionHint, null) : null,
                ];
            }
            return [
                'value' => $key,
                'label' => $item,
                'hint' => null,
            ];
        })
        ->values()
        ->all();

    $sizeClass = match ($size) {
        'sm' => 'edz-select--sm',
        'lg' => 'edz-select--lg',
        default => '',
    };

    $errorClass = $error ? 'edz-select--error' : '';
    $searchable = $search;
    $hasBackendSearch = $attributes->has('wire:search');
?>

<div <?php echo e($attributes->merge(['class' => "edz-select $sizeClass $errorClass $class"])->whereDoesntStartWith('wire:model')->whereDoesntStartWith('x-model')->whereDoesntStartWith('wire:search')); ?>

    x-data="edzSelect({
        options: <?php echo \Illuminate\Support\Js::from($jsOptions)->toHtml() ?>,
        searchable: <?php echo \Illuminate\Support\Js::from($searchable)->toHtml() ?>,
        hasBackendSearch: <?php echo \Illuminate\Support\Js::from($hasBackendSearch)->toHtml() ?>,
        searchMinChars: <?php echo \Illuminate\Support\Js::from($searchMinChars)->toHtml() ?>,
        wireMethodName: <?php echo \Illuminate\Support\Js::from($attributes->get('wire:search'))->toHtml() ?>,
        initialValue: <?php echo \Illuminate\Support\Js::from($attributes->get('wire:model', ''))->toHtml() ?>
    })" x-init="init()">

    <input type="hidden" <?php if($name): ?> name="<?php echo e($name); ?>" <?php endif; ?>
        x-model="selected" x-ref="hiddenInput" <?php echo e($attributes->whereStartsWith('wire:model')); ?>>

    <button type="button" x-ref="trigger" class="edz-select__trigger"
        :class="{ 'edz-select__trigger--disabled': <?php echo \Illuminate\Support\Js::from($disabled)->toHtml() ?>, 'edz-select__trigger--open': open }"
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
                <span>
                    <span x-text="currentLabel"></span>
                    <template x-if="currentHint !== null">
                        <span class="edz-select__hint" x-text="' \u2014 ' + currentHint"></span>
                    </template>
                </span>
            </template>
            <template x-if="currentLabel === null">
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
            <template x-for="(opt, idx) in filteredOptions" :key="opt.id">
                <li role="option" class="edz-select__option"
                    :class="{
                        'edz-select__option--highlighted': highlighted === idx,
                        'edz-select__option--selected': opt.value === selected
                    }"
                    :aria-selected="(opt.value === selected).toString()"
                    @click="select(opt.value)"
                    @mouseenter="highlighted = idx">
                    <span class="edz-select__option-check" x-show="opt.value === selected">
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
                        <span class="edz-select__option-label" x-text="opt.label"></span>
                        <template x-if="opt.hint">
                            <span class="edz-select__option-hint" x-text="opt.hint"></span>
                        </template>
                    </span>
                </li>
            </template>

            <li x-show="loading" class="edz-select__loading">
                <svg class="edz-spinner" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83" />
                </svg>
                <span x-text="'Searching...'"></span>
            </li>

            <li x-show="!loading && filteredOptions.length === 0" class="edz-select__empty">
                <?php echo e(__('merchant_panel.no_products_found')); ?>

            </li>
        </ul>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($error): ?>
        <span class="edz-select__error"><?php echo e($error); ?></span>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>

<script>
    function edzSelect(config) {
        return {
            open: false,
            highlighted: -1,
            query: '',
            selected: null,
            _busy: false,
            openUpward: false,
            popupTop: 0,
            popupLeft: 0,
            popupWidth: 0,
            options: config.options || [],
            backendOptions: [],
            loading: false,
            searchTimeout: null,
            searchable: config.searchable || false,
            hasBackendSearch: config.hasBackendSearch || false,
            searchMinChars: config.searchMinChars || 2,
            wireMethodName: config.wireMethodName || null,

            get allOptions() {
                return [...this.options, ...this.backendOptions];
            },

            get filteredOptions() {
                if (!this.searchable || this.query.trim() === '') return this.allOptions;
                const q = this.query.toLowerCase();
                return this.allOptions.filter(o =>
                    o.label.toLowerCase().includes(q) ||
                    (o.hint && o.hint.toLowerCase().includes(q))
                );
            },

            get currentLabel() {
                const opt = this.allOptions.find(o => o.value === this.selected);
                return opt ? opt.label : null;
            },

            get currentHint() {
                const opt = this.allOptions.find(o => o.value === this.selected);
                return opt && opt.hint ? opt.hint : null;
            },

            get panelStyle() {
                if (!this.open) return 'display:none;';
                const isMobile = window.innerWidth < 640;
                if (isMobile) {
                    const w = Math.min(this.$refs.trigger?.offsetWidth || 300, window.innerWidth - 16);
                    return `position:fixed;bottom:0;left:50%;transform:translateX(-50%);width:${w}px;z-index:70;border-radius:var(--edz-radius-2xl) var(--edz-radius-2xl) 0 0;max-height:60vh;`;
                }
                const s = `position:fixed;z-index:70;width:${this.popupWidth}px;`;
                if (this.openUpward) {
                    return s + `bottom:${window.innerHeight - this.popupTop}px;left:${this.popupLeft}px;`;
                }
                return s + `top:${this.popupTop}px;left:${this.popupLeft}px;`;
            },

            init() {
                this.selected = this.$refs.hiddenInput?.value || null;
            },

            toggle() {
                if (this._busy) return;
                if (this.$el.querySelector('.edz-select__trigger')?.disabled) return;
                this._busy = true;
                setTimeout(() => { this._busy = false; }, 150);

                this.open = !this.open;
                if (this.open) {
                    this.query = '';
                    this.backendOptions = [];
                    this.highlighted = this.allOptions.findIndex(o => o.value === this.selected);
                    this.updatePosition();
                    this.$nextTick(() => {
                        if (this.searchable && this.$refs.searchInput) {
                            this.$refs.searchInput.focus();
                        }
                    });
                }
            },

            close() {
                this.open = false;
            },

            updatePosition() {
                const trigger = this.$refs.trigger;
                if (!trigger) return;
                const rect = trigger.getBoundingClientRect();
                const isMobile = window.innerWidth < 640;

                if (isMobile) return;

                this.popupWidth = rect.width;
                this.popupLeft = rect.left;
                const spaceBelow = window.innerHeight - rect.bottom;
                const spaceAbove = rect.top;

                if (spaceBelow < 280 && spaceAbove > spaceBelow) {
                    this.popupTop = rect.top - 4;
                    this.openUpward = true;
                } else {
                    this.popupTop = rect.bottom + 4;
                    this.openUpward = false;
                }

                if (this.popupLeft + this.popupWidth > window.innerWidth - 8) {
                    this.popupLeft = window.innerWidth - this.popupWidth - 8;
                }
                if (this.popupLeft < 8) {
                    this.popupLeft = 8;
                }
            },

            select(value) {
                if (this._busy) return;
                this._busy = true;
                setTimeout(() => { this._busy = false; }, 200);

                this.selected = value;
                this.open = false;
                this.$nextTick(() => {
                    const input = this.$refs.hiddenInput;
                    if (input) {
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                        input.dispatchEvent(new Event('change', { bubbles: true }));
                        input.dispatchEvent(new CustomEvent('livewire-change', { bubbles: true }));
                    }
                });
            },

            onQueryChange() {
                this.highlighted = this.filteredOptions.length > 0 ? 0 : -1;
                if (!this.hasBackendSearch || !this.wireMethodName) return;

                clearTimeout(this.searchTimeout);
                const q = this.query.trim();
                if (q.length < this.searchMinChars) {
                    this.backendOptions = [];
                    return;
                }
                const localResults = this.options.filter(o =>
                    o.label.toLowerCase().includes(q) ||
                    (o.hint && o.hint.toLowerCase().includes(q))
                );
                if (localResults.length > 0) {
                    this.backendOptions = [];
                    return;
                }
                this.loading = true;
                this.searchTimeout = setTimeout(() => {
                    this.$wire.call(this.wireMethodName, q)
                        .then(results => {
                            this.backendOptions = (results || []).map(r => ({
                                value: r.value ?? r.id ?? r,
                                label: r.label ?? r.name ?? String(r),
                                hint: r.hint ?? null,
                            }));
                            this.loading = false;
                            this.highlighted = this.filteredOptions.length > 0 ? 0 : -1;
                        })
                        .catch(() => { this.loading = false; });
                }, 300);
            },

            moveHighlight(delta) {
                if (!this.open) { this.toggle(); return; }
                const max = this.filteredOptions.length - 1;
                this.highlighted = Math.min(max, Math.max(0, this.highlighted + delta));
            },

            selectHighlighted() {
                const opt = this.filteredOptions[this.highlighted];
                if (opt) this.select(opt.value);
            }
        };
    }
</script>
<?php /**PATH C:\laragon\www\edzeery\resources\views/components/edz/select.blade.php ENDPATH**/ ?>