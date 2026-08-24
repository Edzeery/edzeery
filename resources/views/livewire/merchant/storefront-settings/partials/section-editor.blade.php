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

<div x-data="{ open: false }"
     x-show="$wire.sections.includes('{{ $key }}')"
     class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden transition-all">

    <button type="button"
        x-on:click="open = !open"
        :aria-expanded="open ? 'true' : 'false'"
        :aria-controls="'section-editor-{{ $key }}'"
        class="w-full px-5 py-4 flex items-center justify-between bg-gray-50 dark:bg-gray-800/50 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
        <div class="flex items-center gap-3">
            <x-edz.icon :name="$section['icon']" class="w-5 h-5 text-accent-500" />
            <span class="text-sm font-semibold text-ink">{{ $section['label'] }}</span>
        </div>
        <span x-show="!open"><x-edz.icon name="chevron-down" class="w-5 h-5 text-ink-400" /></span>
        <span x-show="open" x-cloak><x-edz.icon name="chevron-up" class="w-5 h-5 text-ink-400" /></span>
    </button>

    <div id="section-editor-{{ $key }}" role="region" aria-label="{{ $section['label'] }}" x-show="open" x-cloak x-transition.duration.200ms>
        <div class="p-5 space-y-4">
            @if ($fieldsView)
                @include('livewire.merchant.storefront-settings.fields.' . $fieldsView)
            @else
                @include('livewire.merchant.storefront-settings.fields.title-only', ['path' => 'section_content.' . $key])
            @endif
        </div>
    </div>
</div>
