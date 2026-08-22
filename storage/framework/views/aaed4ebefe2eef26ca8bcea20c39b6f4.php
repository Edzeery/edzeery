<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'domain',
    'name'        => null,
    'selected'    => null,
    'set'         => null,
    'placeholder' => null,
    'disabled'    => false,
    'searchable'  => false,
    'size'        => 'md', // sm | md | lg
    'class'       => '',
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
    'domain',
    'name'        => null,
    'selected'    => null,
    'set'         => null,
    'placeholder' => null,
    'disabled'    => false,
    'searchable'  => false,
    'size'        => 'md', // sm | md | lg
    'class'       => '',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $statusManager = app(\Edzeery\MyStatusKit\StatusManager::class);
    $items = $statusManager->domain($domain);

    $iconSet = $set
        ?? config('status-kit-theme.select.default_set')
        ?? config('status-kit-icons.default_set', 'ion');

    $jsOptions = collect($items)->map(fn ($result, $key) => [
        'value' => $key,
        'label' => $result->label(),
        'icon'  => $result->icon($iconSet),
        'hex'   => $result->hex(),
    ])->values()->all();

    $framework = config('status-kit-theme.default_framework', 'bootstrap');
    $classes = config("status-kit-theme.select_classes.{$framework}", config('status-kit-theme.select_classes.bootstrap'));

    $placeholderText = $placeholder ?? __('status-kit::statuses.components.select_placeholder');

    $maxHeight = config('status-kit-theme.select.max_height', '16rem');
    $zIndex    = config('status-kit-theme.select.z_index', 50);
    $uid       = 'status-select-' . \Illuminate\Support\Str::random(8);

    $triggerClass = $size === 'sm'
        ? $classes['trigger_sm']
        : ($size === 'lg' ? $classes['trigger_lg'] : $classes['trigger']);
?>

<?php if (! $__env->hasRenderedOnce('3a2f5e15-38e2-4221-bd16-e45c451ce796')): $__env->markAsRenderedOnce('3a2f5e15-38e2-4221-bd16-e45c451ce796'); ?>
    <style>
        .status-select { position: relative; }
        .status-select-trigger { cursor: pointer; width: 100%; text-align: start; }
        .status-select-trigger.is-disabled { opacity: .6; cursor: not-allowed; pointer-events: none; }
        .status-select-trigger .status-select-chevron { transition: transform .15s ease; }
        .status-select-trigger .status-select-chevron.rotate-180 { transform: rotate(180deg); }
        .status-select-icon svg { width: 1.1em; height: 1.1em; vertical-align: -0.15em; }
        .status-select-dot { width: .55em; height: .55em; border-radius: 50%; flex: none; }
        .status-select-menu {
            position: absolute;
            inset-inline-start: 0;
            top: calc(100% + .25rem);
            min-width: 100%;
            background-color: var(--status-select-bg, var(--bs-body-bg, #fff));
            border: 1px solid var(--status-select-border, var(--bs-border-color, #dee2e6));
            border-radius: var(--status-select-radius, var(--bs-border-radius, .375rem));
            box-shadow: 0 .5rem 1.5rem rgba(0, 0, 0, .15);
            overflow-y: auto;
            padding: .35rem;
            animation: statusSelectPop .15s ease-out;
        }
        @keyframes statusSelectPop {
            from { opacity: 0; transform: translateY(-4px) scale(.97); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }
        .status-select-option {
            display: flex;
            align-items: center;
            gap: .5rem;
            padding: .4rem .55rem;
            border-radius: calc(var(--status-select-radius, var(--bs-border-radius, .375rem)) - 2px);
            cursor: pointer;
        }
        .status-select-option.is-highlighted,
        .status-select-option:hover { background-color: var(--status-select-highlight, var(--bs-tertiary-bg, #f8f9fa)); }
        .status-select-option.is-selected { font-weight: 600; }
    </style>
<?php endif; ?>

<div
    <?php echo e($attributes->whereDoesntStartWith('wire:model')->merge(['class' => trim("status-select $class")])); ?>

    x-data="{
        open: false,
        highlighted: -1,
        query: '',
        searchable: <?php echo e($searchable ? 'true' : 'false'); ?>,
        disabled: <?php echo e($disabled ? 'true' : 'false'); ?>,
        selected: <?php echo \Illuminate\Support\Js::from($selected)->toHtml() ?>,
        options: <?php echo \Illuminate\Support\Js::from($jsOptions)->toHtml() ?>,
        placeholder: <?php echo \Illuminate\Support\Js::from($placeholderText)->toHtml() ?>,
        get filteredOptions() {
            if (! this.searchable || this.query.trim() === '') return this.options;
            const q = this.query.toLowerCase();
            return this.options.filter(o => o.label.toLowerCase().includes(q));
        },
        get current() {
            return this.options.find(o => o.value === this.selected) || null;
        },
        toggle() {
            if (this.disabled) return;
            this.open = ! this.open;
            if (this.open) {
                this.query = '';
                this.highlighted = this.options.findIndex(o => o.value === this.selected);
            }
        },
        select(value) {
            this.selected = value;
            this.open = false;
            this.$nextTick(() => {
                this.$refs.hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
                this.$refs.hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
                this.$refs.hiddenInput.dispatchEvent(new CustomEvent('livewire-change', { bubbles: true }));
            });
        },
        moveHighlight(delta) {
            if (! this.open) { this.toggle(); return; }
            const max = this.filteredOptions.length - 1;
            this.highlighted = Math.min(max, Math.max(0, this.highlighted + delta));
        },
        selectHighlighted() {
            const opt = this.filteredOptions[this.highlighted];
            if (opt) this.select(opt.value);
        },
    }"
    @click.outside="open = false"
>
    <input
        type="hidden"
        x-ref="hiddenInput"
        <?php if($name): ?> name="<?php echo e($name); ?>" <?php endif; ?>
        x-model="selected"
        <?php echo e($attributes->whereStartsWith('wire:model')); ?>

    >

    <button
        type="button"
        id="<?php echo e($uid); ?>-trigger"
        class="status-select-trigger <?php echo e($triggerClass); ?>"
        :class="{ 'is-disabled': disabled }"
        <?php if($disabled): ?> disabled <?php endif; ?>
        role="combobox"
        aria-haspopup="listbox"
        :aria-expanded="open.toString()"
        aria-controls="<?php echo e($uid); ?>-listbox"
        @click="toggle()"
        @keydown.arrow-down.prevent="moveHighlight(1)"
        @keydown.arrow-up.prevent="moveHighlight(-1)"
        @keydown.enter.prevent="open ? selectHighlighted() : toggle()"
        @keydown.escape="open = false"
    >
        <span class="<?php echo e($classes['gap_small']); ?> <?php echo e($classes['overflow']); ?> d-inline-flex align-items-center">
            <template x-if="current">
                <span class="status-select-icon" x-html="current.icon"></span>
            </template>
            <span class="<?php echo e($classes['text_truncate']); ?>" :class="{ '<?php echo e($classes['text_muted']); ?>': ! current }" x-text="current ? current.label : placeholder"></span>
        </span>
        <i class="bi bi-chevron-down status-select-chevron <?php echo e($classes['small']); ?> <?php echo e($classes['ms_2']); ?>" :class="{ 'rotate-180': open }"></i>
    </button>

    <ul
        x-show="open"
        x-cloak
        id="<?php echo e($uid); ?>-listbox"
        role="listbox"
        class="status-select-menu <?php echo e($classes['menu']); ?>"
        style="max-height: <?php echo e($maxHeight); ?>; z-index: <?php echo e($zIndex); ?>;"
    >
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($searchable): ?>
            <li class="<?php echo e($classes['p_1_pb_2']); ?>">
                <input
                    type="text"
                    x-model="query"
                    @click.stop
                    class="<?php echo e($classes['input']); ?>"
                    placeholder="<?php echo e(__('status-kit::statuses.components.select_search')); ?>"
                    aria-label="<?php echo e(__('status-kit::statuses.components.select_search')); ?>"
                >
            </li>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <template x-for="(opt, idx) in filteredOptions" :key="opt.value">
            <li
                role="option"
                class="status-select-option <?php echo e($classes['option']); ?>"
                :class="{ 'is-highlighted': highlighted === idx, 'is-selected': opt.value === selected }"
                :aria-selected="opt.value === selected"
                @click="select(opt.value)"
                @mouseenter="highlighted = idx"
            >
                <span class="status-select-dot" :style="`background-color:${opt.hex}`"></span>
                <span class="status-select-icon" x-html="opt.icon"></span>
                <span class="<?php echo e($classes['flex_grow']); ?>" x-text="opt.label"></span>
                <i class="<?php echo e($classes['check_icon']); ?>" x-show="opt.value === selected"></i>
            </li>
        </template>

        <li x-show="filteredOptions.length === 0" class="<?php echo e($classes['text_muted']); ?> <?php echo e($classes['small']); ?> <?php echo e($classes['px_2_py_1']); ?>">
            <?php echo e(__('status-kit::statuses.components.select_no_results')); ?>

        </li>
    </ul>
</div>
<?php /**PATH C:\laragon\www\edzeery\vendor\edzeery\mystatuskit\resources\views\components\status-select.blade.php ENDPATH**/ ?>