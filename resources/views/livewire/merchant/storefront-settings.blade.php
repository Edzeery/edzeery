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
                ['title' => __('storefront.secure_payment'), 'description' => __('storefront.pay_on_delivery'), 'icon' => 'shield-checkmark-outline'],
                ['title' => __('storefront.fast_delivery'), 'description' => __('storefront.across_the_country'), 'icon' => 'car-outline'],
                ['title' => __('storefront.easy_returns'), 'description' => __('storefront.hassle_free_policy'), 'icon' => 'refresh-outline'],
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

    DB::transaction(function () use ($store) {
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
};

$openPreview = function (): void {
    $this->showPreview = true;
};
?>

<div x-data="{ previewOpen: @entangle('showPreview') }"
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
                        <ion-icon name="storefront-outline" class="text-xl text-accent-600 dark:text-accent-400"></ion-icon>
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
                        <ion-icon :name="copied ? 'checkmark-outline' : 'copy-outline'" class="w-4 h-4 me-1"></ion-icon>
                        <span x-text="copied ? '{{ __('buttons.copied') }}' : '{{ __('buttons.copy_link') }}'"></span>
                    </button>
                    <button type="button" wire:click="openPreview" class="edz-btn edz-btn--primary edz-btn--sm">
                        <ion-icon name="eye-outline" class="w-4 h-4 me-1"></ion-icon>
                        {{ __('storefront.preview') }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    <form wire:submit="save" x-data="edzDirty()">

        {{-- Template Selection --}}
        <div class="edz-card edz-card--padded mb-6">
            <div class="mb-6">
                <h3 class="text-base font-semibold text-ink">{{ __('merchant_panel.storefront_template') }}</h3>
                <p class="text-xs text-ink-400 mt-1">{{ __('merchant_panel.storefront_template_desc') }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach (LandingTemplateEnum::cases() as $case)
                    @php
                        $key = $case->value;
                    @endphp
                    <div class="relative cursor-pointer group"
                         x-on:click="$wire.set('template', '{{ $key }}')"
                         :class="$wire.template === '{{ $key }}' ? 'ring-2 ring-accent-500 shadow-lg shadow-accent-500/10' : 'ring-1 ring-gray-200 dark:ring-gray-700 hover:ring-gray-300 dark:hover:ring-gray-600'">

                        <div class="rounded-xl overflow-hidden transition-all duration-200">

                            <div class="relative h-44 bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900 overflow-hidden">
                                @if ($key === 'single_product')
                                    <div class="absolute inset-0 p-4 flex gap-3">
                                        <div class="flex-1 space-y-2">
                                            <div class="h-2 w-12 rounded bg-accent-400/60"></div>
                                            <div class="h-4 w-3/4 rounded bg-gray-300 dark:bg-gray-600"></div>
                                            <div class="h-3 w-full rounded bg-gray-200 dark:bg-gray-700"></div>
                                            <div class="h-3 w-2/3 rounded bg-gray-200 dark:bg-gray-700"></div>
                                            <div class="h-8 w-24 rounded-lg mt-3 bg-accent-500/80"></div>
                                        </div>
                                        <div class="w-28 h-full rounded-lg bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                            <ion-icon name="image-outline" class="text-2xl text-gray-400 dark:text-gray-500"></ion-icon>
                                        </div>
                                    </div>
                                @elseif ($key === 'catalog')
                                    <div class="absolute inset-0 p-3 flex flex-col gap-2">
                                        <div class="flex gap-1.5">
                                            <div class="h-5 flex-1 rounded-full bg-gray-200 dark:bg-gray-700"></div>
                                            <div class="h-5 flex-1 rounded-full bg-gray-200 dark:bg-gray-700"></div>
                                            <div class="h-5 flex-1 rounded-full bg-gray-200 dark:bg-gray-700"></div>
                                        </div>
                                        <div class="flex-1 grid grid-cols-3 gap-1.5">
                                            @for ($i = 0; $i < 6; $i++)
                                                <div class="rounded bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                                    <ion-icon name="bag-outline" class="text-xs text-gray-400"></ion-icon>
                                                </div>
                                            @endfor
                                        </div>
                                    </div>
                                @else
                                    <div class="absolute inset-0 p-3 flex flex-col gap-2">
                                        <div class="h-8 w-8 rounded-full bg-accent-400/40 mx-auto"></div>
                                        <div class="h-2 w-20 rounded bg-gray-300 dark:bg-gray-600 mx-auto"></div>
                                        <div class="flex-1 grid grid-cols-2 gap-1.5 mt-1">
                                            @for ($i = 0; $i < 4; $i++)
                                                <div class="rounded bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                                    <ion-icon name="bag-outline" class="text-xs text-gray-400"></ion-icon>
                                                </div>
                                            @endfor
                                        </div>
                                    </div>
                                @endif

                                {{-- Selected Badge --}}
                                <div x-show="$wire.template === '{{ $key }}'"
                                     x-transition
                                     class="absolute top-2 right-2 w-6 h-6 rounded-full bg-accent-500 text-white flex items-center justify-center shadow-lg">
                                    <ion-icon name="checkmark-outline" class="text-sm"></ion-icon>
                                </div>
                            </div>

                            <div class="p-4 bg-white dark:bg-gray-800">
                                <p class="text-sm font-semibold text-ink">{{ $case->label() }}</p>
                                <p class="text-xs text-ink-400 mt-1 leading-relaxed">{{ $case->description() }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Theme Colors & Font --}}
        <div class="edz-card edz-card--padded mb-6">
            <div class="mb-6">
                <h3 class="text-base font-semibold text-ink">{{ __('stores.theme') }}</h3>
                <p class="text-xs text-ink-400 mt-1">{{ __('merchant_panel.theme_desc') }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="edz-label">{{ __('stores.primary_color') }}</label>
                    <div class="flex items-center gap-3">
                        <input type="color" wire:model.live="primary_color"
                            class="w-10 h-10 rounded-lg border-2 border-gray-200 dark:border-gray-600 cursor-pointer" />
                        <input type="text" wire:model.live="primary_color"
                            class="edz-input flex-1 font-mono text-sm" placeholder="#4f46e5" />
                    </div>
                </div>

                <div>
                    <label class="edz-label">{{ __('stores.secondary_color') }}</label>
                    <div class="flex items-center gap-3">
                        <input type="color" wire:model.live="secondary_color"
                            class="w-10 h-10 rounded-lg border-2 border-gray-200 dark:border-gray-600 cursor-pointer" />
                        <input type="text" wire:model.live="secondary_color"
                            class="edz-input flex-1 font-mono text-sm" placeholder="#7c3aed" />
                    </div>
                </div>

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

        {{-- Homepage Sections --}}
        <div class="edz-card edz-card--padded mb-6">
            <div class="mb-6">
                <h3 class="text-base font-semibold text-ink">{{ __('merchant_panel.homepage_sections') }}</h3>
                <p class="text-xs text-ink-400 mt-1">{{ __('merchant_panel.homepage_sections_desc') }}</p>
            </div>

            @php
                $availableSections = [
                    'hero'         => ['label' => __('merchant_panel.section_hero'),         'description' => __('merchant_panel.section_hero_desc'),         'icon' => 'image-outline'],
                    'social_proof' => ['label' => __('merchant_panel.section_social_proof'), 'description' => __('merchant_panel.section_social_proof_desc'), 'icon' => 'shield-checkmark-outline'],
                    'faq'          => ['label' => __('merchant_panel.section_faq'),           'description' => __('merchant_panel.section_faq_desc'),           'icon' => 'help-circle-outline'],
                    'cta'          => ['label' => __('merchant_panel.section_cta'),            'description' => __('merchant_panel.section_cta_desc'),            'icon' => 'megaphone-outline'],
                    'categories'   => ['label' => __('merchant_panel.section_categories'),    'description' => __('merchant_panel.section_categories_desc'),    'icon' => 'grid-outline'],
                    'brands'       => ['label' => __('merchant_panel.section_brands'),        'description' => __('merchant_panel.section_brands_desc'),        'icon' => 'ribbon-outline'],
                    'description'  => ['label' => __('merchant_panel.section_description'),   'description' => __('merchant_panel.section_description_desc'),   'icon' => 'document-text-outline'],
                ];
            @endphp

            {{-- Section Toggles --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 mb-6">
                @foreach ($availableSections as $key => $section)
                    <label class="flex items-start gap-3 p-3.5 rounded-xl border transition-all duration-200 cursor-pointer
                        {{ in_array($key, $sections) ? 'border-accent-500 bg-accent-50 dark:bg-accent-900/10 shadow-sm' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600' }}">
                        <input type="checkbox" name="sections[]" value="{{ $key }}"
                               wire:model.live="sections"
                               class="mt-0.5 rounded border-gray-300 text-accent-600 focus:ring-accent-500" />
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <ion-icon name="{{ $section['icon'] }}" class="text-base text-ink-400 shrink-0"></ion-icon>
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
                    <ion-icon name="create-outline" class="text-lg text-ink-400"></ion-icon>
                    <h4 class="text-sm font-semibold text-ink">{{ __('merchant_panel.section_content') }}</h4>
                </div>
                <p class="text-xs text-ink-400 mb-5">{{ __('merchant_panel.section_content_desc') }}</p>

                @php
                    $sectionIcons = [
                        'hero'         => 'image-outline',
                        'social_proof' => 'shield-checkmark-outline',
                        'faq'          => 'help-circle-outline',
                        'cta'          => 'megaphone-outline',
                        'categories'   => 'grid-outline',
                        'brands'       => 'ribbon-outline',
                        'description'  => 'document-text-outline',
                    ];
                @endphp

                <div class="space-y-3">
                    @foreach ($availableSections as $key => $section)
                        @if (in_array($key, $sections))
                            <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden transition-all"
                                 x-data="{ open: $wire.expanded_section === '{{ $key }}' }">
                                {{-- Accordion Header --}}
                                <button type="button"
                                    x-on:click="
                                        open = !open;
                                        $wire.set('expanded_section', open ? '{{ $key }}' : '');
                                    "
                                    class="w-full px-5 py-4 flex items-center justify-between bg-gray-50 dark:bg-gray-800/50 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                                    <div class="flex items-center gap-3">
                                        <ion-icon name="{{ $sectionIcons[$key] }}" class="text-lg text-accent-500"></ion-icon>
                                        <span class="text-sm font-semibold text-ink">{{ $section['label'] }}</span>
                                        @if (!empty($section_content[$key]))
                                            <span class="w-2 h-2 rounded-full bg-emerald-400 shrink-0" title="Customized"></span>
                                        @endif
                                    </div>
                                    <ion-icon :name="open ? 'chevron-up-outline' : 'chevron-down-outline'" class="text-ink-400 transition-transform" x-bind:class="open && 'rotate-180'"></ion-icon>
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

        {{-- Save --}}
        <div class="flex items-center justify-between">
            <button type="button" wire:click="openPreview"
                class="edz-btn edz-btn--secondary">
                <ion-icon name="eye-outline" class="w-4 h-4 me-1"></ion-icon>
                {{ __('storefront.preview_template') }}
            </button>
            <button type="submit" class="edz-btn edz-btn--primary">
                <ion-icon name="save-outline" class="w-4 h-4 me-1"></ion-icon>
                {{ __('merchant_panel.save_template') }}
            </button>
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
                            <ion-icon name="eye-outline" class="text-lg text-accent-600 dark:text-accent-400"></ion-icon>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-ink">{{ __('storefront.preview') }} — {{ currentStore()->name }}</p>
                            <p class="text-xs text-ink-400 font-mono">{{ currentStore()->public_url }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ currentStore()->public_url . '?preview=1' }}" target="_blank" rel="noopener noreferrer"
                           class="edz-btn edz-btn--secondary edz-btn--sm">
                            <ion-icon name="open-outline" class="w-4 h-4 me-1"></ion-icon>
                            {{ __('storefront.open_in_new_tab') }}
                        </a>
                        <button type="button" @click="previewOpen = false; $wire.set('showPreview', false)"
                            class="p-2 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition">
                            <ion-icon name="close-outline" class="text-xl text-ink-400"></ion-icon>
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
