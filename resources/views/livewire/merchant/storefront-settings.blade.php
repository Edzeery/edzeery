<?php

use App\Enums\Store\LandingTemplateEnum;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Stores\Store;
use App\Models\Stores\StoreThemeSetting;
use Illuminate\Support\Facades\DB;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;

layout('components.layouts.store');

state([
    'template'        => '',
    'sections'        => [],
    'primary_color'   => '#4f46e5',
    'secondary_color' => '#7c3aed',
    'font_family'     => 'Cairo',
    'showPreview'     => false,
    'section_content' => [],
    'expanded_section' => '',
]);

mount(function (): void {
    abort_unless(canStore(StorePermissionEnum::STORE_UPDATE->value), 403);

    $store = currentStore();
    abort_unless($store, 404);

    $defaults = [
        'hero' => ['title' => '', 'description' => '', 'button_text' => ''],
        'social_proof' => [
            'title' => __('storefront.why_customers_love_us'),
            'items' => [
                ['title' => __('storefront.secure_payment'), 'description' => __('storefront.pay_on_delivery'), 'icon' => 'shield-check'],
                ['title' => __('storefront.fast_delivery'), 'description' => __('storefront.across_the_country'), 'icon' => 'arrow-right'],
                ['title' => __('storefront.easy_returns'), 'description' => __('storefront.hassle_free_policy'), 'icon' => 'refresh'],
            ],
        ],
        'faq' => [
            'title' => __('storefront.faq'),
            'items' => [
                ['question' => __('storefront.faq_delivery_q'), 'answer' => __('storefront.faq_delivery_a')],
                ['question' => __('storefront.faq_payment_q'), 'answer' => __('storefront.faq_payment_a')],
                ['question' => __('storefront.faq_return_q'), 'answer' => __('storefront.faq_return_a')],
            ],
        ],
        'cta' => ['title' => __('storefront.ready_to_order'), 'description' => __('storefront.get_yours_now'), 'button_text' => __('storefront.order_now')],
        'categories' => ['title' => __('storefront.categories')],
        'brands' => ['title' => __('storefront.collections')],
        'description' => ['title' => __('storefront.product_details')],
    ];

    $this->template = $store->landing_template?->value ?? LandingTemplateEnum::SINGLE_PRODUCT->value;
    $theme = $store->theme;
    if ($theme) {
        $this->primary_color = $theme->primary_color ?? '#4f46e5';
        $this->secondary_color = $theme->secondary_color ?? '#7c3aed';
        $this->font_family = $theme->font_family ?? 'Cairo';
        $this->sections = $theme->homepage_sections ?? ['hero', 'social_proof', 'faq', 'cta'];
        $this->section_content = $theme->section_content ?? $defaults;
    } else {
        $this->section_content = $defaults;
    }
});

$save = function (): void {
    $store = currentStore();
    abort_unless($store, 404);

    try {
        DB::transaction(function () use ($store) {
            if (! in_array($this->template, \App\Enums\Store\LandingTemplateEnum::values(), true)) {
                throw new \Exception('Invalid template');
            }
            $store->update(['landing_template' => $this->template]);

            $store->theme()->updateOrCreate([], [
                'primary_color'     => $this->primary_color,
                'secondary_color'   => $this->secondary_color,
                'font_family'       => $this->font_family,
                'homepage_sections' => $this->sections,
                'section_content'   => $this->section_content,
            ]);
        });

        $this->dispatch('swal', type: 'success', title: __('merchant_panel.template_updated'));
    } catch (\Throwable $e) {
        $this->dispatch('swal', type: 'error', title: __('merchant_panel.save_failed'), text: $e->getMessage());
    }
};

$openPreview = function (): void {
    $this->showPreview = true;
};
?>

<div x-data="{
        previewOpen: @entangle('showPreview'),
        activeTab: 'template',
        selectedTemplate: @js($this->template),
        previewUrl: '{{ currentStore()?->isPubliclyActive() ? currentStore()->public_url : '#' }}'
    }"
     x-init="$watch('$wire.template', v => selectedTemplate = v)"
     x-on:open-preview.window="previewOpen = true"
     x-on:close-preview.window="previewOpen = false">

    <x-edz.page-header
        title="{{ __('merchant_panel.storefront_template') }}"
        description="{{ __('merchant_panel.storefront_template_desc') }}">
    </x-edz.page-header>

    {{-- Store Link Bar --}}
    @if (currentStore()?->isPubliclyActive())
        <div class="mb-6 p-4 bg-accent-50 dark:bg-accent-900/20 border border-accent-200 dark:border-accent-800 rounded-xl">
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-accent-100 dark:bg-accent-800/50 flex items-center justify-center">
                        <x-edz.icon name="storefront" class="w-5 h-5 text-accent-600 dark:text-accent-400" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-accent-700 dark:text-accent-300">{{ __('storefront.your_store_link') }}</p>
                        <p class="text-xs text-accent-500 dark:text-accent-400 font-mono">{{ currentStore()->public_url }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button"
                        x-data="{ copied: false }"
                        x-on:click="navigator.clipboard.writeText('{{ currentStore()->public_url }}'); copied = true; setTimeout(() => copied = false, 2000)"
                        class="edz-btn edz-btn--secondary edz-btn--sm">
                        <span x-show="!copied"><x-edz.icon name="copy" class="w-4 h-4 me-1" /></span>
                        <span x-show="copied" x-cloak><x-edz.icon name="check" class="w-4 h-4 me-1" /></span>
                        <span x-text="copied ? '{{ __('buttons.copied') }}' : '{{ __('buttons.copy_link') }}'"></span>
                    </button>
                    <button type="button" @click="$dispatch('open-preview')"
                        class="edz-btn edz-btn--primary edz-btn--sm">
                        <x-edz.icon name="eye" class="w-4 h-4 me-1" />
                        {{ __('storefront.preview') }} {{ __('merchant_panel.store') }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    <form wire:submit="save" x-data="{ ...edzDirty(), activeTab: 'template' }">

        <div class="flex flex-col lg:flex-row gap-6">

            {{-- ======================== --}}
            {{-- Mobile: Horizontal Tabs --}}
            {{-- ======================== --}}
            <div class="lg:hidden shrink-0">
                <div class="flex gap-1 p-1 bg-gray-100 dark:bg-gray-800 rounded-xl overflow-x-auto">
                    @php
                        $tabs = [
                            'template' => ['icon' => 'color-palette', 'label' => __('merchant_panel.tab_template')],
                            'design'   => ['icon' => 'swatch',        'label' => __('merchant_panel.tab_design')],
                            'sections' => ['icon' => 'list-bullet',   'label' => __('merchant_panel.tab_sections')],
                        ];
                    @endphp
                    @foreach ($tabs as $tabKey => $tab)
                        <button type="button"
                            x-on:click="activeTab = '{{ $tabKey }}'"
                            class="flex items-center gap-1.5 px-4 py-2.5 rounded-lg text-sm font-medium whitespace-nowrap transition-all duration-200 flex-1 justify-center"
                            :class="activeTab === '{{ $tabKey }}'
                                ? 'bg-white dark:bg-gray-700 text-accent-600 dark:text-accent-400 shadow-sm'
                                : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'">
                            <x-edz.icon :name="$tab['icon']" class="w-4 h-4 shrink-0" />
                            <span>{{ $tab['label'] }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- ======================= --}}
            {{-- Desktop: Vertical Tabs --}}
            {{-- ======================= --}}
            <div class="hidden lg:block w-64 shrink-0">
                <div class="edz-card p-2 sticky top-6">
                    @php
                        $tabs = [
                            'template' => ['icon' => 'color-palette', 'label' => __('merchant_panel.tab_template'),  'desc' => __('merchant_panel.tab_template_desc')],
                            'design'   => ['icon' => 'swatch',        'label' => __('merchant_panel.tab_design'),    'desc' => __('merchant_panel.tab_design_desc')],
                            'sections' => ['icon' => 'list-bullet',   'label' => __('merchant_panel.tab_sections'),  'desc' => __('merchant_panel.tab_sections_desc')],
                        ];
                    @endphp
                    <nav class="space-y-1">
                        @foreach ($tabs as $tabKey => $tab)
                            <button type="button"
                                x-on:click="activeTab = '{{ $tabKey }}'"
                                class="w-full flex items-center gap-3 px-3.5 py-3 rounded-xl text-start transition-all duration-200 group"
                                :class="activeTab === '{{ $tabKey }}'
                                    ? 'bg-accent-50 dark:bg-accent-900/15 text-accent-700 dark:text-accent-300'
                                    : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/50 hover:text-gray-900 dark:hover:text-gray-200'">
                                <div class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0 transition-colors duration-200"
                                     :class="activeTab === '{{ $tabKey }}'
                                         ? 'bg-accent-100 dark:bg-accent-800/40'
                                         : 'bg-gray-100 dark:bg-gray-800 group-hover:bg-gray-200 dark:group-hover:bg-gray-700'">
                                    <x-edz.icon :name="$tab['icon']" class="w-5 h-5" />
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold leading-tight"
                                       :class="activeTab === '{{ $tabKey }}' ? 'text-accent-800 dark:text-accent-200' : ''">
                                        {{ $tab['label'] }}
                                    </p>
                                    <p class="text-[11px] leading-tight mt-0.5 truncate"
                                       :class="activeTab === '{{ $tabKey }}' ? 'text-accent-500/70 dark:text-accent-400/60' : 'text-gray-400 dark:text-gray-500'">
                                        {{ $tab['desc'] }}
                                    </p>
                                </div>
                            </button>
                        @endforeach
                    </nav>
                </div>
            </div>

            {{-- ========== --}}
            {{-- Tab Panels --}}
            {{-- ========== --}}
            <div class="flex-1 min-w-0">

                {{-- ============================== --}}
                {{-- Tab 1: Template Selection      --}}
                {{-- ============================== --}}
                <div x-show="activeTab === 'template'"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="edz-card edz-card--padded">
                        <div class="mb-6">
                            <h3 class="text-base font-semibold text-ink">{{ __('merchant_panel.storefront_template') }}</h3>
                            <p class="text-xs text-ink-muted mt-1">{{ __('merchant_panel.storefront_template_desc') }}</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                            @foreach (LandingTemplateEnum::cases() as $case)
                                @php
                                    $key = $case->value;
                                @endphp
                                <div class="relative cursor-pointer group"
                                     x-on:click="selectedTemplate = '{{ $key }}'; $wire.set('template', '{{ $key }}')">

                                    <div class="rounded-2xl overflow-hidden transition-all duration-200"
                                         :class="selectedTemplate === '{{ $key }}'
                                             ? 'ring-2 ring-accent-500 shadow-lg'
                                             : 'ring-1 ring-gray-200 dark:ring-gray-700 hover:ring-gray-300 dark:hover:ring-gray-600'">

                                        {{-- Thumbnail --}}
                                        <div class="relative h-40 bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900 overflow-hidden">
                                            @if ($key === 'single_product')
                                                <div class="absolute inset-0 p-4 flex gap-3">
                                                    <div class="flex-1 space-y-2.5">
                                                        <div class="h-2.5 w-14 rounded-full bg-accent-400/70"></div>
                                                        <div class="h-4 w-4/5 rounded-lg bg-gray-300 dark:bg-gray-600"></div>
                                                        <div class="h-3 w-full rounded bg-gray-200 dark:bg-gray-700"></div>
                                                        <div class="h-3 w-3/4 rounded bg-gray-200 dark:bg-gray-700"></div>
                                                        <div class="mt-4 space-y-2">
                                                            <div class="h-2 w-16 rounded bg-gray-200 dark:bg-gray-700"></div>
                                                            <div class="h-3 w-24 rounded bg-gray-300 dark:bg-gray-600"></div>
                                                        </div>
                                                        <div class="h-9 w-28 rounded-xl bg-accent-500/90 mt-2"></div>
                                                    </div>
                                                    <div class="w-28 h-full rounded-xl bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                                        <x-edz.icon name="image" class="w-8 h-8 text-gray-400 dark:text-gray-500" />
                                                    </div>
                                                </div>
                                            @elseif ($key === 'catalog')
                                                <div class="absolute inset-0 p-3 flex flex-col gap-2.5">
                                                    <div class="flex gap-1.5">
                                                        <div class="h-6 flex-1 rounded-full bg-accent-400/40 border border-accent-300/50"></div>
                                                        <div class="h-6 flex-1 rounded-full bg-gray-200 dark:bg-gray-700"></div>
                                                        <div class="h-6 flex-1 rounded-full bg-gray-200 dark:bg-gray-700"></div>
                                                    </div>
                                                    <div class="h-6 rounded-lg bg-gray-200 dark:bg-gray-700 w-1/2"></div>
                                                    <div class="flex-1 grid grid-cols-3 gap-1.5">
                                                        @for ($i = 0; $i < 6; $i++)
                                                            <div class="rounded-lg bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                                                <x-edz.icon name="bag" class="w-4 h-4 text-gray-400 dark:text-gray-500" />
                                                            </div>
                                                        @endfor
                                                    </div>
                                                    <div class="h-5 w-16 rounded-full bg-accent-500/60 mx-auto"></div>
                                                </div>
                                            @else
                                                <div class="absolute inset-0 p-3 flex flex-col items-center gap-2">
                                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-accent-400 to-accent-600 mx-auto"></div>
                                                    <div class="h-2 w-24 rounded bg-gray-300 dark:bg-gray-600 mx-auto"></div>
                                                    <div class="h-2 w-16 rounded bg-gray-200 dark:bg-gray-700 mx-auto"></div>
                                                    <div class="flex-1 w-full grid grid-cols-2 gap-1.5 mt-2">
                                                        @for ($i = 0; $i < 4; $i++)
                                                            <div class="rounded-lg bg-gray-200 dark:bg-gray-700 flex flex-col items-center justify-center gap-1 p-1">
                                                                <div class="w-6 h-6 rounded bg-gray-300 dark:bg-gray-600"></div>
                                                                <div class="h-1 w-8 rounded bg-gray-300 dark:bg-gray-600"></div>
                                                            </div>
                                                        @endfor
                                                    </div>
                                                </div>
                                            @endif

                                            {{-- Selected Badge --}}
                                            <div class="absolute top-2.5 right-2.5 w-7 h-7 rounded-full flex items-center justify-center shadow-lg transition-all duration-200"
                                                 :class="selectedTemplate === '{{ $key }}'
                                                     ? 'bg-accent-500 text-white scale-100 opacity-100'
                                                     : 'bg-gray-400/50 text-white scale-75 opacity-0'">
                                                <x-edz.icon name="check" class="w-4 h-4" />
                                            </div>
                                        </div>

                                        {{-- Card Footer --}}
                                        <div class="p-4 bg-white dark:bg-gray-800">
                                            <p class="text-sm font-semibold text-ink">{{ $case->label() }}</p>
                                            <p class="text-xs text-ink-muted mt-1 leading-relaxed">{{ $case->description() }}</p>
                                            <a :href="previewUrl + '?preview_template={{ $key }}'"
                                               target="_blank" rel="noopener noreferrer"
                                               x-on:click.stop
                                               class="mt-3 inline-flex items-center gap-1 text-xs font-medium text-accent-600 dark:text-accent-400 hover:text-accent-700 dark:hover:text-accent-300 transition">
                                                 <x-edz.icon name="eye" class="w-4 h-4" />
                                                {{ __('storefront.preview_template') }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- ============================== --}}
                {{-- Tab 2: Design (Colors & Font)  --}}
                {{-- ============================== --}}
                <div x-show="activeTab === 'design'"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="edz-card edz-card--padded">
                        <div class="mb-6">
                            <h3 class="text-base font-semibold text-ink">{{ __('stores.theme') }}</h3>
                            <p class="text-xs text-ink-muted mt-1">{{ __('merchant_panel.theme_desc') }}</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                            {{-- Primary Color --}}
                            <div>
                                <label class="edz-label">{{ __('stores.primary_color') }}</label>
                                <div class="flex items-center gap-3">
                                    <input type="color" wire:model.live="primary_color"
                                        class="w-10 h-10 rounded-lg border-2 border-gray-200 dark:border-gray-600 cursor-pointer shrink-0" />
                                    <input type="text" wire:model.live="primary_color"
                                        class="edz-input flex-1 font-mono text-sm" placeholder="#4f46e5" />
                                </div>
                            </div>

                            {{-- Secondary Color --}}
                            <div>
                                <label class="edz-label">{{ __('stores.secondary_color') }}</label>
                                <div class="flex items-center gap-3">
                                    <input type="color" wire:model.live="secondary_color"
                                        class="w-10 h-10 rounded-lg border-2 border-gray-200 dark:border-gray-600 cursor-pointer shrink-0" />
                                    <input type="text" wire:model.live="secondary_color"
                                        class="edz-input flex-1 font-mono text-sm" placeholder="#7c3aed" />
                                </div>
                            </div>

                            {{-- Font Family --}}
                            <div>
                                <label class="edz-label">{{ __('stores.font_family') }}</label>
                                <select wire:model.live="font_family" class="edz-input">
                                    <option value="Cairo">Cairo</option>
                                    <option value="Tajawal">Tajawal</option>
                                    <option value="Inter">Inter</option>
                                    <option value="Poppins">Poppins</option>
                                    <option value="Playfair Display">Playfair Display</option>
                                    <option value="Montserrat">Montserrat</option>
                                    <option value="Lato">Lato</option>
                                    <option value="Nunito">Nunito</option>
                                </select>
                            </div>
                        </div>

                        {{-- Live Preview --}}
                        <div class="mt-6 p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                            <p class="text-xs text-ink-400 mb-3 font-medium">{{ __('merchant_panel.live_preview') }}</p>
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
                                <span class="text-sm text-ink" :style="'font-family: ' + $wire.font_family + ', sans-serif'">
                                    {{ __('storefront.products') }} Aa
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ============================== --}}
                {{-- Tab 3: Homepage Sections       --}}
                {{-- ============================== --}}
                <div x-show="activeTab === 'sections'"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="edz-card edz-card--padded">
                        <div class="mb-6">
                            <h3 class="text-base font-semibold text-ink">{{ __('merchant_panel.homepage_sections') }}</h3>
                            <p class="text-xs text-ink-muted mt-1">{{ __('merchant_panel.homepage_sections_desc') }}</p>
                        </div>

                        @php
                            $availableSections = [
                                'hero'         => ['label' => __('merchant_panel.section_hero'),         'description' => __('merchant_panel.section_hero_desc'),         'icon' => 'image'],
                                'social_proof' => ['label' => __('merchant_panel.section_social_proof'), 'description' => __('merchant_panel.section_social_proof_desc'), 'icon' => 'shield-check'],
                                'faq'          => ['label' => __('merchant_panel.section_faq'),           'description' => __('merchant_panel.section_faq_desc'),           'icon' => 'help-circle'],
                                'cta'          => ['label' => __('merchant_panel.section_cta'),            'description' => __('merchant_panel.section_cta_desc'),            'icon' => 'megaphone'],
                                'categories'   => ['label' => __('merchant_panel.section_categories'),    'description' => __('merchant_panel.section_categories_desc'),    'icon' => 'grid'],
                                'brands'       => ['label' => __('merchant_panel.section_brands'),        'description' => __('merchant_panel.section_brands_desc'),        'icon' => 'ribbon'],
                                'description'  => ['label' => __('merchant_panel.section_description'),   'description' => __('merchant_panel.section_description_desc'),   'icon' => 'document-text'],
                            ];
                        @endphp

                        {{-- Section Toggles --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-6">
                            @foreach ($availableSections as $key => $section)
                                <label class="flex items-start gap-3 p-3.5 rounded-xl border transition-all duration-200 cursor-pointer
                                    {{ in_array($key, $sections) ? 'border-accent-500 bg-accent-50 dark:bg-accent-900/10 shadow-sm' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600' }}">
                                    <input type="checkbox" name="sections[]" value="{{ $key }}"
                                           wire:model.live="sections"
                                           class="mt-0.5 rounded border-gray-300 text-accent-600 focus:ring-accent-500" />
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <x-edz.icon :name="$section['icon']" class="w-4 h-4 text-ink-400 shrink-0" />
                                            <p class="text-sm font-medium text-ink truncate">{{ $section['label'] }}</p>
                                        </div>
                                        <p class="text-xs text-ink-400 mt-0.5">{{ $section['description'] }}</p>
                                    </div>
                                </label>
                            @endforeach
                        </div>

                        {{-- Section Content Editing --}}
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                            <div class="flex items-center gap-2 mb-4">
                                <x-edz.icon name="edit" class="w-5 h-5 text-ink-400" />
                                <h4 class="text-sm font-semibold text-ink">{{ __('merchant_panel.section_content') }}</h4>
                            </div>
                            <p class="text-xs text-ink-400 mb-5">{{ __('merchant_panel.section_content_desc') }}</p>

                            @php
                                $sectionIcons = [
                                    'hero'         => 'image',
                                    'social_proof' => 'shield-check',
                                    'faq'          => 'help-circle',
                                    'cta'          => 'megaphone',
                                    'categories'   => 'grid',
                                    'brands'       => 'ribbon',
                                    'description'  => 'document-text',
                                ];
                            @endphp

                            <div class="space-y-3">
                                @foreach ($availableSections as $key => $section)
                                    @if (in_array($key, $sections))
                                        <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden transition-all"
                                             x-data="{ open: $wire.expanded_section === '{{ $key }}' }">
                                            {{-- Accordion Header --}}
                                            <button type="button"
                                                x-on:click="open = !open; $wire.set('expanded_section', open ? '{{ $key }}' : '');"
                                                class="w-full px-5 py-4 flex items-center justify-between bg-gray-50 dark:bg-gray-800/50 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                                                <div class="flex items-center gap-3">
                                                    <x-edz.icon :name="$sectionIcons[$key]" class="w-5 h-5 text-accent-500" />
                                                    <span class="text-sm font-semibold text-ink">{{ $section['label'] }}</span>
                                                    @if (!empty($section_content[$key]))
                                                        <span class="w-2 h-2 rounded-full bg-emerald-400 shrink-0"></span>
                                                    @endif
                                                </div>
                                                <span x-show="!open"><x-edz.icon name="chevron-down" class="w-5 h-5 text-ink-400" /></span>
                                                <span x-show="open" x-cloak><x-edz.icon name="chevron-up" class="w-5 h-5 text-ink-400" /></span>
                                            </button>

                                            {{-- Accordion Content --}}
                                            <div x-show="open" x-transition.duration.200ms>
                                                <div class="p-5 space-y-4">
                                                    @if ($key === 'hero')
                                                        <div>
                                                            <label class="edz-label">{{ __('merchant_panel.hero_title') }}</label>
                                                            <input type="text"
                                                                wire:model.live="section_content.hero.title"
                                                                class="edz-input"
                                                                placeholder="{{ __('merchant_panel.hero_title_placeholder') }}" />
                                                        </div>
                                                        <div>
                                                            <label class="edz-label">{{ __('merchant_panel.hero_description') }}</label>
                                                            <textarea wire:model.live="section_content.hero.description"
                                                                class="edz-input" rows="2"
                                                                placeholder="{{ __('merchant_panel.hero_description_placeholder') }}"></textarea>
                                                        </div>
                                                        <div>
                                                            <label class="edz-label">{{ __('merchant_panel.hero_button_text') }}</label>
                                                            <input type="text"
                                                                wire:model.live="section_content.hero.button_text"
                                                                class="edz-input"
                                                                placeholder="{{ __('storefront.order_now') }}" />
                                                        </div>

                                                    @elseif ($key === 'social_proof')
                                                        <div>
                                                            <label class="edz-label">{{ __('merchant_panel.section_title') }}</label>
                                                            <input type="text"
                                                                wire:model.live="section_content.social_proof.title"
                                                                class="edz-input" />
                                                        </div>
                                                        @foreach ([0, 1, 2] as $i)
                                                            <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-800/30 border border-gray-100 dark:border-gray-700 space-y-3">
                                                                <p class="text-xs font-medium text-ink-400 uppercase tracking-wider">{{ __('merchant_panel.item') }} {{ $i + 1 }}</p>
                                                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                                    <div>
                                                                        <label class="edz-label text-xs">{{ __('merchant_panel.item_title') }}</label>
                                                                        <input type="text"
                                                                            wire:model.live="section_content.social_proof.items.{{ $i }}.title"
                                                                            class="edz-input" />
                                                                    </div>
                                                                    <div>
                                                                        <label class="edz-label text-xs">{{ __('merchant_panel.item_description') }}</label>
                                                                        <input type="text"
                                                                            wire:model.live="section_content.social_proof.items.{{ $i }}.description"
                                                                            class="edz-input" />
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach

                                                    @elseif ($key === 'faq')
                                                        <div>
                                                            <label class="edz-label">{{ __('merchant_panel.section_title') }}</label>
                                                            <input type="text"
                                                                wire:model.live="section_content.faq.title"
                                                                class="edz-input" />
                                                        </div>
                                                        @foreach ([0, 1, 2] as $i)
                                                            <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-800/30 border border-gray-100 dark:border-gray-700 space-y-3">
                                                                <p class="text-xs font-medium text-ink-400 uppercase tracking-wider">{{ __('merchant_panel.faq_item') }} {{ $i + 1 }}</p>
                                                                <div>
                                                                    <label class="edz-label text-xs">{{ __('merchant_panel.question') }}</label>
                                                                    <input type="text"
                                                                        wire:model.live="section_content.faq.items.{{ $i }}.question"
                                                                        class="edz-input" />
                                                                </div>
                                                                <div>
                                                                    <label class="edz-label text-xs">{{ __('merchant_panel.answer') }}</label>
                                                                    <textarea wire:model.live="section_content.faq.items.{{ $i }}.answer"
                                                                        class="edz-input" rows="2"></textarea>
                                                                </div>
                                                            </div>
                                                        @endforeach

                                                    @elseif ($key === 'cta')
                                                        <div>
                                                            <label class="edz-label">{{ __('merchant_panel.cta_title') }}</label>
                                                            <input type="text"
                                                                wire:model.live="section_content.cta.title"
                                                                class="edz-input"
                                                                placeholder="{{ __('storefront.ready_to_order') }}" />
                                                        </div>
                                                        <div>
                                                            <label class="edz-label">{{ __('merchant_panel.cta_description') }}</label>
                                                            <input type="text"
                                                                wire:model.live="section_content.cta.description"
                                                                class="edz-input"
                                                                placeholder="{{ __('storefront.get_yours_now') }}" />
                                                        </div>
                                                        <div>
                                                            <label class="edz-label">{{ __('merchant_panel.hero_button_text') }}</label>
                                                            <input type="text"
                                                                wire:model.live="section_content.cta.button_text"
                                                                class="edz-input"
                                                                placeholder="{{ __('storefront.order_now') }}" />
                                                        </div>

                                                    @elseif ($key === 'categories')
                                                        <div>
                                                            <label class="edz-label">{{ __('merchant_panel.section_title') }}</label>
                                                            <input type="text"
                                                                wire:model.live="section_content.categories.title"
                                                                class="edz-input" />
                                                        </div>

                                                    @elseif ($key === 'brands')
                                                        <div>
                                                            <label class="edz-label">{{ __('merchant_panel.section_title') }}</label>
                                                            <input type="text"
                                                                wire:model.live="section_content.brands.title"
                                                                class="edz-input" />
                                                        </div>

                                                    @elseif ($key === 'description')
                                                        <div>
                                                            <label class="edz-label">{{ __('merchant_panel.section_title') }}</label>
                                                            <input type="text"
                                                                wire:model.live="section_content.description.title"
                                                                class="edz-input" />
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- Save Bar --}}
        <div class="sticky bottom-0 mt-6 -mx-4 px-4 py-4 bg-white/80 dark:bg-gray-900/80 backdrop-blur-lg border-t border-gray-200 dark:border-gray-700/50 z-10">
            <div class="flex items-center justify-end gap-3">
                @if (currentStore()?->isPubliclyActive())
                    <a href="{{ currentStore()->public_url }}" target="_blank" rel="noopener noreferrer"
                       class="edz-btn edz-btn--secondary edz-btn--sm hidden sm:inline-flex">
                        <x-edz.icon name="external-link" class="w-4 h-4 me-1" />
                        {{ __('storefront.open_store') }}
                    </a>
                @endif
                <button type="submit" class="edz-btn edz-btn--primary">
                    <x-edz.icon name="save" class="w-4 h-4 me-1" />
                    {{ __('merchant_panel.save_template') }}
                </button>
            </div>
        </div>
    </form>

    {{-- Preview Modal --}}
    @if (currentStore()?->isPubliclyActive())
        <div x-show="previewOpen" x-cloak
             class="fixed inset-0 z-[100] flex items-center justify-center p-4"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="previewOpen = false; $wire.set('showPreview', false)"></div>
            <div class="relative w-full h-[90vh] max-w-7xl bg-white dark:bg-gray-900 rounded-2xl shadow-2xl overflow-hidden flex flex-col"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">

                <div class="flex items-center justify-between px-5 py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-accent-100 dark:bg-accent-900/30 flex items-center justify-center">
                            <x-edz.icon name="eye" class="w-5 h-5 text-accent-600 dark:text-accent-400" />
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-ink">{{ __('storefront.preview') }} — {{ currentStore()->name }}</p>
                            <p class="text-xs text-ink-400 font-mono">{{ currentStore()->public_url }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ currentStore()->public_url . '?preview=1' }}" target="_blank" rel="noopener noreferrer"
                           class="edz-btn edz-btn--secondary edz-btn--sm">
                        <x-edz.icon name="external-link" class="w-4 h-4 me-1" />
                            {{ __('storefront.open_in_new_tab') }}
                        </a>
                        <button type="button" @click="previewOpen = false; $wire.set('showPreview', false)"
                            class="p-2 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition">
                            <x-edz.icon name="x-mark" class="w-5 h-5 text-ink-400" />
                        </button>
                    </div>
                </div>

                <div class="flex-1 relative bg-white">
                    <iframe
                        x-ref="previewFrame"
                        x-show="previewOpen"
                        src="{{ currentStore()->public_url . '?preview=1' }}"
                        class="absolute inset-0 w-full h-full border-0"
                        loading="lazy"
                    ></iframe>
                </div>
            </div>
        </div>
    @endif
</div>
