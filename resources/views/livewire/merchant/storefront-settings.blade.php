<?php

use App\Enums\Store\LandingTemplateEnum;
use App\Enums\Store\StorePermissionEnum;
use App\Services\Stores\StorefrontThemeService;
use App\Support\Storefront\StorefrontSections;
use Illuminate\Validation\ValidationException;
use function Livewire\Volt\computed;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;

layout('components.layouts.store');

/*
|--------------------------------------------------------------------------
| State ownership policy
|--------------------------------------------------------------------------
| - Livewire : persisted business data ONLY. Every wire:model below is
|   deferred -> zero network requests while editing; data is committed
|   with the single "save" submission.
| - Alpine   : ephemeral UI state (tabs / accordion / modal / clipboard).
|   Never duplicated into Livewire state.
*/

state([
    'template' => LandingTemplateEnum::SINGLE_PRODUCT->value,
    'sections' => [],
    'primary_color' => StorefrontSections::DEFAULT_PRIMARY_COLOR,
    'secondary_color' => StorefrontSections::DEFAULT_SECONDARY_COLOR,
    'font_family' => StorefrontSections::DEFAULT_FONT_FAMILY,
    'section_content' => [],
    // Ephemeral search term for the single-product picker (live, not persisted).
    'picker_query' => '',
]);

mount(function (): void {
    abort_unless(canStore(StorePermissionEnum::STORE_UPDATE->value), 403);

    $store = currentStore();
    abort_unless($store, 404);

    $storedTemplate = $store->landing_template?->value;

    $this->template = in_array($storedTemplate, LandingTemplateEnum::values(), true) ? $storedTemplate : LandingTemplateEnum::SINGLE_PRODUCT->value;

    $theme = $store->theme;

    $this->primary_color = $theme?->primary_color ?? StorefrontSections::DEFAULT_PRIMARY_COLOR;
    $this->secondary_color = $theme?->secondary_color ?? StorefrontSections::DEFAULT_SECONDARY_COLOR;
    $this->font_family = $theme?->font_family ?? StorefrontSections::DEFAULT_FONT_FAMILY;

    $enabled = is_array($theme?->homepage_sections) ? $theme->homepage_sections : StorefrontSections::DEFAULT_ENABLED;

    $this->sections = array_values(array_intersect(StorefrontSections::ALL, $enabled));

    if ($this->sections === []) {
        $this->sections = StorefrontSections::DEFAULT_ENABLED;
    }

    // Guarantees the full contract shape so every nested wire:model binds safely.
    $this->section_content = StorefrontSections::normalize($theme?->section_content);
});

$save = function (): void {
    abort_unless(canStore(StorePermissionEnum::STORE_UPDATE->value), 403);

    $store = currentStore();
    abort_unless($store, 404);

    try {
        $payload = $this->only(['template', 'primary_color', 'secondary_color', 'font_family', 'sections', 'section_content']);

        // Stale clients may submit removed section keys -> sanitize, don't fail.
        $payload['sections'] = array_values(array_intersect(StorefrontSections::ALL, is_array($payload['sections'] ?? null) ? $payload['sections'] : []));

        // Contract first: validation rules assume the normalized shape.
        $payload['section_content'] = StorefrontSections::normalize($payload['section_content'] ?? null);

        // Ownership guard: a stale/foreign/deactivated product choice silently
        // falls back to automatic — same philosophy as unknown section keys.
        $chosenProductId = (string) ($payload['section_content']['single_product']['product_id'] ?? '');
        if ($chosenProductId !== '') {
            $ownsChosen = \App\Models\Products\Product::whereKey($chosenProductId)->where('store_id', $store->id)->where('is_active', true)->exists();

            if (!$ownsChosen) {
                $payload['section_content']['single_product']['product_id'] = '';
            }
        }

        $validated = validator($payload, [
            'template' => ['required', 'string', 'in:' . implode(',', LandingTemplateEnum::values())],
            ...StorefrontSections::validationRules(),
        ])->validate();

        StorefrontThemeService::save($store, $validated['template'], [
            'primary_color' => $validated['primary_color'],
            'secondary_color' => $validated['secondary_color'],
            'font_family' => $validated['font_family'],
            'homepage_sections' => array_values(array_intersect(StorefrontSections::ALL, $validated['sections'])),
            'section_content' => $validated['section_content'],
        ]);

        // Reflect normalized truth back into the editor.
        $this->section_content = $validated['section_content'];

        $this->dispatch('swal', type: 'success', title: __('merchant_panel.template_updated'));
    } catch (ValidationException $e) {
        $this->dispatch('swal', type: 'error', title: __('merchant_panel.save_failed'), text: $e->validator->errors()->first());

        throw $e;
    } catch (\Throwable $e) {
        report($e);

        $this->dispatch('swal', type: 'error', title: __('merchant_panel.save_failed'));
    }
};

$resetSection = function (string $key): void {
    abort_unless(canStore(StorePermissionEnum::STORE_UPDATE->value), 403);

    // Unknown/stale keys are ignored silently — same philosophy as save().
    if (!in_array($key, StorefrontSections::ALL, true)) {
        return;
    }

    // Replace whole array (never mutate offsets in place) so Livewire
    // reliably diffs and re-renders the affected editors.
    $content = $this->section_content;
    $content[$key] = StorefrontSections::defaults()[$key];
    $this->section_content = $content;
};

$productsForPicker = computed(function () {
    $store = currentStore();

    if (!$store) {
        return collect();
    }

    return \App\Models\Products\Product::query()
        ->where('store_id', $store->id)
        ->where('is_active', true)
        ->orderByDesc('created_at')
        ->limit(200)
        ->get(['id', 'name']);
});

$pickerOptions = computed(function () {
    $query = trim((string) $this->picker_query);

    return $this->productsForPicker
        ->when($query !== '', fn($c) => $c->filter(fn($p) => str_contains(mb_strtolower($p->name), mb_strtolower($query))))
        ->take(20)
        ->values();
});

$chosenPickerProduct = computed(function () {
    $chosenId = (string) ($this->section_content['single_product']['product_id'] ?? '');

    if ($chosenId === '') {
        return null;
    }

    return \App\Models\Products\Product::query()->where('store_id', currentStore()?->id)->find($chosenId);
});

?>
@php
    $store = currentStore();
    $canPreview = $store && $store->isPubliclyActive();

    $tabs = [
        'template' => [
            'icon' => 'color-palette',
            'label' => __('merchant_panel.tab_template'),
            'desc' => __('merchant_panel.tab_template_desc'),
        ],
        'design' => [
            'icon' => 'swatch',
            'label' => __('merchant_panel.tab_design'),
            'desc' => __('merchant_panel.tab_design_desc'),
        ],
        'sections' => [
            'icon' => 'list-bullet',
            'label' => __('merchant_panel.tab_sections'),
            'desc' => __('merchant_panel.tab_sections_desc'),
        ],
    ];

    $sectionConfig = [
        'hero' => ['icon' => 'image', 'label' => __('merchant_panel.section_hero')],
        'social_proof' => ['icon' => 'shield-check', 'label' => __('merchant_panel.section_social_proof')],
        'faq' => ['icon' => 'help-circle', 'label' => __('merchant_panel.section_faq')],
        'cta' => ['icon' => 'megaphone', 'label' => __('merchant_panel.section_cta')],
        'categories' => ['icon' => 'grid', 'label' => __('merchant_panel.section_categories')],
        'brands' => ['icon' => 'ribbon', 'label' => __('merchant_panel.section_brands')],
        'description' => ['icon' => 'document-text', 'label' => __('merchant_panel.section_description')],
    ];

    $sectionDescriptions = [
        'hero' => __('merchant_panel.section_hero_desc'),
        'social_proof' => __('merchant_panel.section_social_proof_desc'),
        'faq' => __('merchant_panel.section_faq_desc'),
        'cta' => __('merchant_panel.section_cta_desc'),
        'categories' => __('merchant_panel.section_categories_desc'),
        'brands' => __('merchant_panel.section_brands_desc'),
        'description' => __('merchant_panel.section_description_desc'),
    ];
@endphp

{{-- Interactivity policy: NO Blade interpolation inside any JS-bearing
     attribute. Server values travel through plain data-* attributes;
     every x-data / x-on expression below is a hand-written literal, so
     translations or URLs can never break attribute quoting again. --}}
<div x-data="{
    activeTab: 'template',
    selectedTemplate: null,
    previewOpen: false,

    get previewUrl() { return this.$root.dataset.previewUrl || '' },

    selectTemplate(key) {
        this.selectedTemplate = key;
        this.$wire.template = key;
    },
    openPreview() {
        if (!this.previewUrl) { return; }
        this.previewOpen = true;
    }
}" x-init="selectedTemplate = $root.dataset.selectedTemplate" data-selected-template="{{ $template }}"
    data-preview-url="{{ $canPreview ? $store->public_url : '' }}"
    x-effect="document.documentElement.style.overflow = previewOpen ? 'hidden' : ''">

    {{-- Clipboard helper: defined once, plain JS, no quoting hazards --}}
    <script>
        window.edzCopy = window.edzCopy || function(text) {
            if (navigator.clipboard && window.isSecureContext) {
                return navigator.clipboard.writeText(text);
            }

            return new Promise(function(resolve) {
                var helper = document.createElement('textarea');
                helper.value = text;
                document.body.appendChild(helper);
                helper.select();
                try {
                    document.execCommand('copy');
                } finally {
                    helper.remove();
                }
                resolve();
            });
        };
    </script>

    <x-edz.page-header title="{{ __('merchant_panel.storefront_template') }}"
        description="{{ __('merchant_panel.storefront_template_desc') }}">
    </x-edz.page-header>

    {{-- Store Link Bar --}}
    @if ($canPreview)
        <div
            class="mb-6 p-4 bg-accent-surface border border-accent-border rounded-xl">
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-xl bg-accent-surface-strong flex items-center justify-center">
                        <x-edz.icon name="storefront" class="w-5 h-5 text-accent-fg" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-accent-fg-strong">
                            {{ __('storefront.your_store_link') }}</p>
                        <p class="text-xs text-accent-fg font-mono">{{ $store->public_url }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" data-copy-url="{{ $store->public_url }}" x-data="{ copied: false }"
                        x-on:click="window.edzCopy($el.dataset.copyUrl).then(function () {
                            this.copied = true;
                            setTimeout(function () { this.copied = false }, 2000);
                        }.bind(this))"
                        class="edz-btn edz-btn--secondary edz-btn--sm">
                        <span x-show="!copied">
                            <x-edz.icon name="copy" class="w-4 h-4 me-1" />
                        </span>
                        <span x-show="copied" x-cloak>
                            <x-edz.icon name="check" class="w-4 h-4 me-1" /></span>
                        <span x-show="!copied">{{ __('buttons.copy_link') }}</span>
                        <span x-show="copied" x-cloak>{{ __('buttons.copied') }}</span>
                    </button>
                    <button type="button" x-on:click="openPreview()" data-preview-url="{{ $store->public_url }}"
                        class="edz-btn edz-btn--primary edz-btn--sm">
                        <x-edz.icon name="eye" class="w-4 h-4 me-1" />
                        {{ __('storefront.preview') }} {{ __('merchant_panel.store') }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    <form wire:submit="save">

        <div class="flex flex-col lg:flex-row gap-6">

            {{-- Mobile tabs --}}
            <div class="lg:hidden shrink-0">
                @include('livewire.merchant.storefront-settings.partials.tabs-nav', [
                    'tabs' => $tabs,
                    'variant' => 'mobile',
                ])
            </div>

            {{-- Desktop tabs --}}
            <div class="hidden lg:block w-64 shrink-0">
                <div class="edz-card p-2 sticky top-6">
                    @include('livewire.merchant.storefront-settings.partials.tabs-nav', [
                        'tabs' => $tabs,
                        'variant' => 'desktop',
                    ])
                </div>
            </div>

            {{-- Tab panels --}}
            <div class="flex-1 min-w-0">

                {{-- Tab 1: Template selection --}}
                <div id="tab-panel-template" role="tabpanel" aria-labelledby="tab-btn-template" tabindex="0"
                    x-show="activeTab === 'template'" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="edz-card edz-card--padded">
                        <div class="mb-6">
                            <h3 class="text-base font-semibold text-ink">{{ __('merchant_panel.storefront_template') }}
                            </h3>
                            <p class="text-xs text-ink-muted mt-1">{{ __('merchant_panel.storefront_template_desc') }}
                            </p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                            @foreach (LandingTemplateEnum::cases() as $case)
                                @php $key = $case->value; @endphp
                                <div class="relative cursor-pointer group"
                                    x-on:click="selectTemplate('{{ $key }}')"
                                    x-bind:data-selected="selectedTemplate === '{{ $key }}' ? 'true' : null">

                                    <div class="rounded-2xl overflow-hidden transition-all duration-200"
                                        :class="selectedTemplate === '{{ $key }}'
                                            ?
                                            'ring-2 ring-accent-500 shadow-lg' :
                                            'ring-1 ring-gray-ring hover:ring-surface-border-strong'">

                                        <div
                                            class="relative h-40 bg-gradient-to-br from-gray-50 to-gray-100 overflow-hidden">
                                            @include(
                                                'livewire.merchant.storefront-settings.partials.template-thumbnails',
                                                ['key' => $key]
                                            )

                                            <div class="absolute top-2.5 right-2.5 w-7 h-7 rounded-full flex items-center justify-center shadow-lg transition-all duration-200"
                                                :class="selectedTemplate === '{{ $key }}'
                                                    ?
                                                    'bg-accent-500 text-white scale-100 opacity-100' :
                                                    'bg-gray-400/50 text-white scale-75 opacity-0'">
                                                <x-edz.icon name="check" class="w-5 h-5" />
                                            </div>
                                        </div>

                                        <div class="p-4 bg-surface">
                                            <p class="text-sm font-semibold text-ink">{{ $case->label() }}</p>
                                            <p class="text-xs text-ink-muted mt-1 leading-relaxed">
                                                {{ $case->description() }}</p>
                                            @if ($canPreview)
                                                <a :href="'{{ $store->public_url }}?preview_template={{ $key }}'"
                                                    target="_blank" rel="noopener noreferrer" x-on:click.stop
                                                    class="mt-3 inline-flex items-center gap-1 text-xs font-medium text-accent-fg hover:text-accent-fg-strong transition">
                                                    <x-edz.icon name="eye" class="w-4 h-4" />
                                                    {{ __('storefront.preview_template') }}
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Single-product template: which product to showcase.
                             Every choice commits LIVE so the selection is
                             visible (and survives a refresh) immediately —
                             no hidden deferred state, no save surprises. --}}
                        <div class="mt-6 pt-6 border-t border-surface-border"
                            x-show="selectedTemplate === 'single_product'" x-cloak x-data="{ open: false }"
                            @click.outside="open = false">
                            <label class="edz-label">{{ __('merchant_panel.template_product') }}</label>

                            @php $chosenPickerProduct = $this->chosenPickerProduct; @endphp

                            <div class="relative sm:max-w-md">
                                <button type="button" x-on:click="open = !open"
                                    class="edz-input w-full flex items-center justify-between text-start"
                                    aria-haspopup="listbox" :aria-expanded="open">
                                    <span class="truncate flex items-center gap-2 min-w-0">
                                        @if ($chosenPickerProduct)
                                            <x-edz.icon name="check-circle"
                                                class="w-4 h-4 store-text-primary shrink-0" />
                                            <span class="truncate font-medium">{{ $chosenPickerProduct->name }}</span>
                                        @else
                                            <span
                                                class="text-ink-muted">{{ __('merchant_panel.template_product_auto') }}</span>
                                        @endif
                                    </span>
                                    <x-edz.icon name="chevron-down"
                                        class="w-4 h-4 text-gray-400 shrink-0 ms-2 transition-transform duration-200"
                                        x-bind:class="open ? 'rotate-180' : ''" />
                                </button>

                                <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-150"
                                    x-transition:enter-start="opacity-0 -translate-y-1"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    class="absolute z-30 mt-2 w-full rounded-xl border border-surface-border bg-surface shadow-xl overflow-hidden">
                                    <div class="p-2 border-b border-surface-border">
                                        <input type="search" wire:model.live.debounce.250ms="picker_query"
                                            placeholder="{{ __('storefront.search_products') }}"
                                            class="edz-input w-full text-sm">
                                    </div>

                                    <ul class="max-h-56 overflow-y-auto py-1" role="listbox">
                                        <li>
                                            <button type="button" data-picker-value=""
                                                x-on:click="$wire.set('section_content.single_product.product_id', $el.dataset.pickerValue); $wire.set('picker_query', ''); open = false"
                                                class="w-full text-start px-3 py-2.5 text-sm hover:bg-surface-secondary transition flex items-center justify-between gap-2 {{ $chosenPickerProduct ? '' : 'store-text-primary font-semibold' }}">
                                                <span>{{ __('merchant_panel.template_product_auto') }}</span>
                                                @if (!$chosenPickerProduct)
                                                    <x-edz.icon name="check" class="w-4 h-4 shrink-0" />
                                                @endif
                                            </button>
                                        </li>
                                        @foreach ($this->pickerOptions as $pickerProduct)
                                            @php $isChosen = (string) $chosenPickerProduct?->id === (string) $pickerProduct->id; @endphp
                                            <li wire:key="picker-opt-{{ $pickerProduct->id }}">
                                                <button type="button" data-picker-value="{{ $pickerProduct->id }}"
                                                    x-on:click="$wire.set('section_content.single_product.product_id', $el.dataset.pickerValue); $wire.set('picker_query', ''); open = false"
                                                    class="w-full text-start px-3 py-2.5 text-sm hover:bg-surface-secondary transition flex items-center justify-between gap-2 {{ $isChosen ? 'store-text-primary font-semibold' : '' }}">
                                                    <span class="truncate">{{ $pickerProduct->name }}</span>
                                                    @if ($isChosen)
                                                        <x-edz.icon name="check" class="w-4 h-4 shrink-0" />
                                                    @endif
                                                </button>
                                            </li>
                                        @endforeach
                                        @if ($this->pickerOptions->isEmpty())
                                            <li class="px-3 py-3 text-sm text-ink-muted">
                                                {{ __('storefront.no_results_found') }}
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </div>

                            <p class="text-xs text-ink-muted mt-1">{{ __('merchant_panel.template_product_hint') }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Tab 2: Design --}}
                <div id="tab-panel-design" role="tabpanel" aria-labelledby="tab-btn-design" tabindex="0"
                    x-show="activeTab === 'design'" x-cloak x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="edz-card edz-card--padded">
                        <div class="mb-6">
                            <h3 class="text-base font-semibold text-ink">{{ __('stores.theme') }}</h3>
                            <p class="text-xs text-ink-muted mt-1">{{ __('merchant_panel.theme_desc') }}</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                            <div>
                                <label class="edz-label" for="primary-color">{{ __('stores.primary_color') }}</label>
                                <div class="flex items-center gap-3">
                                    <input id="primary-color" type="color" wire:model="primary_color"
                                        class="w-10 h-10 rounded-lg border-2 border-surface-border cursor-pointer shrink-0" />
                                    <input type="text" wire:model="primary_color"
                                        class="edz-input flex-1 font-mono text-sm" placeholder="#4f46e5" />
                                </div>
                            </div>

                            <div>
                                <label class="edz-label"
                                    for="secondary-color">{{ __('stores.secondary_color') }}</label>
                                <div class="flex items-center gap-3">
                                    <input id="secondary-color" type="color" wire:model="secondary_color"
                                        class="w-10 h-10 rounded-lg border-2 border-surface-border cursor-pointer shrink-0" />
                                    <input type="text" wire:model="secondary_color"
                                        class="edz-input flex-1 font-mono text-sm" placeholder="#7c3aed" />
                                </div>
                            </div>

                            <div>
                                <label class="edz-label" for="font-family">{{ __('stores.font_family') }}</label>
                                <select id="font-family" wire:model="font_family" class="edz-input">
                                    @foreach (StorefrontSections::FONTS as $font)
                                        <option value="{{ $font }}">{{ $font }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Live preview: reads reactive $wire state locally (no requests) --}}
                        <div
                            class="mt-6 p-4 rounded-xl border border-surface-border bg-surface-secondary/50">
                            <p class="text-xs text-ink-400 mb-3 font-medium">{{ __('merchant_panel.live_preview') }}
                            </p>
                            <div class="flex items-center gap-3 flex-wrap">
                                <div class="h-10 px-5 rounded-lg text-white text-sm font-semibold flex items-center"
                                    :style="'background-color: ' + $wire.primary_color">
                                    {{ __('storefront.add_to_cart') }}
                                </div>
                                <div class="h-10 px-5 rounded-lg text-white text-sm font-semibold flex items-center"
                                    :style="'background-color: ' + $wire.secondary_color">
                                    {{ __('storefront.checkout') }}
                                </div>
                                <div class="h-10 px-5 rounded-lg border-2 text-sm font-semibold flex items-center"
                                    :style="'border-color: ' + $wire.primary_color + '; color: ' + $wire.primary_color">
                                    {{ __('storefront.options') }}
                                </div>
                                <span class="text-sm text-ink"
                                    :style="'font-family: \'' + $wire.font_family + '\', sans-serif'">
                                    {{ __('storefront.products') }} Aa
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tab 3: Homepage sections --}}
                <div id="tab-panel-sections" role="tabpanel" aria-labelledby="tab-btn-sections" tabindex="0"
                    x-show="activeTab === 'sections'" x-cloak x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="edz-card edz-card--padded">
                        <div class="mb-6">
                            <h3 class="text-base font-semibold text-ink">{{ __('merchant_panel.homepage_sections') }}
                            </h3>
                            <p class="text-xs text-ink-muted mt-1">{{ __('merchant_panel.homepage_sections_desc') }}
                            </p>
                        </div>

                        {{-- Section toggles --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-6">
                            @foreach ($sectionConfig as $key => $section)
                                @if ($key === 'hero')
                                    {{-- single_product ignores hero content entirely;
                                         hide the toggle so merchants never edit dead fields. --}}
                                    <div x-show="$wire.template !== 'single_product'">
                                @endif
                                <label
                                    class="flex items-start gap-3 p-3.5 rounded-xl border transition-all duration-200 cursor-pointer"
                                    :class="$wire.sections.includes('{{ $key }}') ?
                                        'border-accent-500 bg-accent-surface-subtle shadow-sm' :
                                        'border-surface-border hover:border-surface-border-strong'">
                                    <input type="checkbox" value="{{ $key }}" wire:model="sections"
                                        class="mt-0.5 rounded border-gray-300 text-accent-600 focus:ring-accent-500" />
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <x-edz.icon :name="$section['icon']" class="w-4 h-4 text-ink-400 shrink-0" />
                                            <p class="text-sm font-medium text-ink truncate">{{ $section['label'] }}
                                            </p>
                                        </div>
                                        <p class="text-xs text-ink-400 mt-0.5">{{ $sectionDescriptions[$key] }}</p>
                                    </div>
                                </label>
                                @if ($key === 'hero')
                        </div>
                        @endif
                        @endforeach
                    </div>

                    {{-- Section content editors --}}
                    <div class="border-t border-surface-border pt-6">
                        <div class="flex items-center gap-2 mb-4">
                            <x-edz.icon name="edit" class="w-4 h-4 text-ink-400" />
                            <h4 class="text-sm font-semibold text-ink">{{ __('merchant_panel.section_content') }}
                            </h4>
                        </div>
                        <p class="text-xs text-ink-400 mb-5">{{ __('merchant_panel.section_content_desc') }}</p>

                        <div class="space-y-3">
                            @foreach ($sectionConfig as $key => $section)
                                @if ($key === 'hero')
                                    <div x-show="$wire.template !== 'single_product'">
                                @endif
                                @include('livewire.merchant.storefront-settings.partials.section-editor', [
                                    'key' => $key,
                                    'section' => $section,
                                    'description' => $sectionDescriptions[$key],
                                ])
                                @if ($key === 'hero')
                        </div>
                        @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

</div>
</div>

{{-- Save bar --}}
<div
    class="sticky bottom-0 mt-6 -mx-4 px-4 py-4 bg-surface/80 backdrop-blur-lg border-t border-surface-border/50 z-10">
    <div class="flex items-center justify-end gap-3">
        @if ($canPreview)
            <a href="{{ $store->public_url }}" target="_blank" rel="noopener noreferrer"
                class="edz-btn edz-btn--secondary edz-btn--sm hidden sm:inline-flex">
                <x-edz.icon name="external-link" class="w-4 h-4 me-1" />
                {{ __('storefront.open_store') }}
            </a>
        @endif
        <button type="submit" wire:target="save" wire:loading.attr="disabled"
            class="edz-btn edz-btn--primary disabled:opacity-60 disabled:cursor-not-allowed">
            <span wire:loading.remove wire:target="save" class="inline-flex items-center">
                <x-edz.icon name="save" class="w-4 h-4 me-1" />
            </span>
            <span wire:loading wire:target="save" class="inline-flex items-center">
                <x-edz.icon name="arrow-path" class="w-4 h-4 me-1 animate-spin" />
            </span>
            {{ __('merchant_panel.save_template') }}
        </button>
    </div>
</div>
</form>

@if ($canPreview)
    @include('livewire.merchant.storefront-settings.partials.preview-modal', ['store' => $store])
@endif
</div>
