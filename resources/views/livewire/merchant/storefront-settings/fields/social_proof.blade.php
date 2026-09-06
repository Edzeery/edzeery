@php
    $limits = \App\Support\Storefront\StorefrontSections::TEXT_LIMITS;
@endphp
@include('livewire.merchant.storefront-settings.fields.partials.countered-field', [
    'id' => 'social-proof-title',
    'label' => __('merchant_panel.section_title'),
    'wirePath' => 'section_content.social_proof.title',
    'max' => $limits['title'],
])
@foreach ([0, 1, 2] as $i)
    @php
        // Defensive: editor state may be partial mid-edit (tests / stale clients).
        $item = $section_content['social_proof']['items'][$i] ?? [];
    @endphp
    <div class="p-3 rounded-lg bg-surface-secondary/30 border border-surface-border space-y-3">
        <p class="text-xs font-medium text-ink-400 uppercase tracking-wider">{{ __('merchant_panel.item') }} {{ $i + 1 }}</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            @include('livewire.merchant.storefront-settings.fields.partials.countered-field', [
                'id' => 'social-proof-item-' . $i . '-title',
                'label' => __('merchant_panel.item_title'),
                'wirePath' => 'section_content.social_proof.items.' . $i . '.title',
                'max' => $limits['item_title'],
            ])
            @include('livewire.merchant.storefront-settings.fields.partials.countered-field', [
                'id' => 'social-proof-item-' . $i . '-description',
                'label' => __('merchant_panel.item_description'),
                'wirePath' => 'section_content.social_proof.items.' . $i . '.description',
                'max' => $limits['item_description'],
            ])
        </div>

        {{-- Icon picker: server values travel via data-* attributes only;
             the deferred $wire.set keeps editing request-free until save. --}}
        <div class="flex items-center gap-3 relative" x-data="{ open: false }" data-item-index="{{ $i }}">
            <input type="hidden" wire:model="section_content.social_proof.items.{{ $i }}.icon" />
            <span class="text-xs font-medium text-ink-400 shrink-0">{{ __('merchant_panel.item_icon') }}</span>
            <span class="w-9 h-9 rounded-md bg-brand-surface-strong flex items-center justify-center text-accent-fg shrink-0">
                <x-edz.icon :name="($item['icon'] ?? '') !== '' ? $item['icon'] : 'grid'" class="w-5 h-5" />
            </span>
            <button type="button"
                class="edz-btn edz-btn--ghost edz-btn--sm shrink-0"
                x-on:click="open = !open"
                :aria-expanded="open ? 'true' : 'false'"
                aria-haspopup="true">
                {{ __('merchant_panel.choose_icon') }}
                <x-edz.icon name="chevron-down" class="w-3 h-3" />
            </button>

            {{-- Last item opens upward so the popup never collides with the
                 sticky save bar or the page edge. --}}
            <div x-show="open" x-cloak
                class="fixed inset-0 z-[205] bg-black/40 backdrop-blur-sm sm:hidden" @click="open = false"></div>
            <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-3"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-3"
                class="fixed inset-x-0 bottom-0 z-[210] w-full rounded-t-2xl border border-b-0 border-surface-border bg-surface
                       p-3 pb-[calc(1rem+env(safe-area-inset-bottom))] shadow-[0_-16px_48px_-12px_rgba(15,23,42,.25)]
                       max-h-[70vh] overflow-y-auto edz-scroll grid grid-cols-7 gap-1
                       sm:absolute sm:inset-x-auto sm:bottom-auto sm:z-20 sm:w-[min(22rem,90vw)] sm:p-3
                       sm:rounded-xl sm:border sm:border-surface-border sm:shadow-lg sm:bg-surface
                       {{ $i === 2 ? 'sm:bottom-full sm:mb-2' : 'sm:top-full sm:mt-2' }}"
                role="listbox" aria-label="{{ __('merchant_panel.choose_icon') }}">
                <span class="pointer-events-none col-span-7 mx-auto mb-2 block h-1 w-10 rounded-full bg-surface-border sm:hidden"></span>
                <div class="flex items-center justify-between gap-2 col-span-7 px-1 mb-2 sm:hidden">
                    <p class="inline-flex items-center gap-1.5 text-xs font-semibold text-ink uppercase tracking-wide">
                        <x-edz.icon name="grid" class="w-3.5 h-3.5 text-ink-muted" />
                        <span>{{ __('merchant_panel.choose_icon') }}</span>
                    </p>
                    <button @click="open = false" type="button"
                        class="-m-1 p-1 rounded-lg text-ink-muted hover:text-ink hover:bg-surface-tertiary"
                        title="{{ __('general.close') }}">
                        <x-edz.icon name="x-mark" class="w-4 h-4" />
                    </button>
                </div>
                @foreach (\App\Support\Storefront\StorefrontSections::ICONS as $iconName)
                    <button type="button"
                        data-icon="{{ $iconName }}"
                        title="{{ $iconName }}"
                        class="p-2 rounded-lg hover:bg-brand-surface-strong flex items-center justify-center text-ink-600 text-ink-soft"
                        x-on:click="(async () => { await $wire.set('section_content.social_proof.items.' + $root.dataset.itemIndex + '.icon', $el.dataset.icon, true); open = false })()">
                        <x-edz.icon name="{{ $iconName }}" class="w-5 h-5" />
                    </button>
                @endforeach
            </div>
        </div>
    </div>
@endforeach
