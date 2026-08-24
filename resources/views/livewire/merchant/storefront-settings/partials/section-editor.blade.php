{{-- Accordion shell: always rendered so deferred bindings stay live,
     visibility driven client-side by the enabled-sections state. --}}
@php
    $fieldsViews = [
        'hero'         => 'hero',
        'social_proof' => 'social_proof',
        'faq'          => 'faq',
        'cta'          => 'cta',
    ];

    $fieldsView = $fieldsViews[$key] ?? null;
@endphp

{{-- No overflow-hidden here: it would clip absolutely-positioned popups
     (icon picker) that extend past the panel edge. --}}
<div x-data="{ open: false }"
     x-show="$wire.sections.includes('{{ $key }}')"
     class="border border-gray-200 dark:border-gray-700 rounded-xl transition-all">

    <button type="button"
        x-on:click="open = !open"
        :aria-expanded="open ? 'true' : 'false'"
        :aria-controls="'section-editor-{{ $key }}'"
        class="w-full px-5 py-4 flex items-center justify-between bg-gray-50 dark:bg-gray-800/50 hover:bg-gray-100 dark:hover:bg-gray-800 transition rounded-t-xl">
        <div class="flex items-center gap-3">
            <x-edz.icon :name="$section['icon']" class="w-5 h-5 text-accent-500" />
            <span class="text-sm font-semibold text-ink">{{ $section['label'] }}</span>
        </div>
        <span x-show="!open"><x-edz.icon name="chevron-down" class="w-5 h-5 text-ink-400" /></span>
        <span x-show="open" x-cloak><x-edz.icon name="chevron-up" class="w-5 h-5 text-ink-400" /></span>
    </button>

    <div id="section-editor-{{ $key }}" role="region" aria-label="{{ $section['label'] }}" x-show="open" x-cloak x-transition.duration.200ms>
        <div class="p-5 space-y-4">
            <div class="flex justify-end">
                <button type="button"
                    x-data
                    data-reset-key="{{ $key }}"
                    data-confirm-title="{{ __('merchant_panel.reset_section') }}"
                    data-confirm-text="{{ __('merchant_panel.reset_section_confirm') }}"
                    x-on:click.prevent="(async () => { if (await EdzSwal.confirmAction($el.dataset.confirmTitle, $el.dataset.confirmText)) await $wire.resetSection($el.dataset.resetKey) })()"
                    class="edz-btn edz-btn--ghost edz-btn--xs text-ink-400 hover:text-accent-600">
                    <x-edz.icon name="arrow-path" class="w-3 h-3 me-1" />
                    {{ __('merchant_panel.reset_section') }}
                </button>
            </div>
            @if ($fieldsView)
                @include('livewire.merchant.storefront-settings.fields.' . $fieldsView)
            @else
                @include('livewire.merchant.storefront-settings.fields.title-only', ['path' => 'section_content.' . $key, 'id' => $key . '-title'])
            @endif
        </div>
    </div>
</div>
