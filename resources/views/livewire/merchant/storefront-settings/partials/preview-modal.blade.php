{{-- Preview modal: mounted only while open (template x-if) so the
     iframe is never loaded eagerly and is destroyed on close. --}}
<template x-if="previewOpen">
    <div role="dialog" aria-modal="true"
         aria-label="{{ __('storefront.preview') }} — {{ $store->name }}"
         class="fixed inset-0 z-[100] flex items-center justify-center p-4"
         x-on:keydown.escape.window="previewOpen = false"
         x-init="$nextTick(() => $el.querySelector('[data-modal-autofocus]')?.focus())"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" x-on:click="previewOpen = false"></div>

        <div class="relative w-full h-[90vh] max-w-7xl bg-surface rounded-2xl shadow-2xl overflow-hidden flex flex-col"
             x-on:click.stop
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">

            <div class="flex items-center justify-between px-5 py-3 border-b border-surface-border bg-surface-secondary shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-accent-surface flex items-center justify-center">
                        <x-edz.icon name="eye" class="w-5 h-5 text-accent-fg" />
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-ink">{{ __('storefront.preview') }} — {{ $store->name }}</p>
                        <p class="text-xs text-ink-400 font-mono">{{ $store->public_url }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ $store->public_url }}?preview=1" target="_blank" rel="noopener noreferrer"
                       class="edz-btn edz-btn--secondary edz-btn--sm">
                        <x-edz.icon name="external-link" class="w-4 h-4 me-1" />
                        {{ __('storefront.open_in_new_tab') }}
                    </a>
                    <button type="button" data-modal-autofocus
                        x-on:click="previewOpen = false"
                        aria-label="{{ __('buttons.close') }}"
                        class="p-2 rounded-lg hover:bg-surface-secondary transition">
                        <x-edz.icon name="x-mark" class="w-5 h-5 text-ink-400" />
                    </button>
                </div>
            </div>

            <div class="flex-1 relative bg-white">
                <iframe
                    title="{{ __('storefront.preview') }}"
                    :src="previewUrl + '?preview=1'"
                    class="absolute inset-0 w-full h-full border-0"></iframe>
            </div>
        </div>
    </div>
</template>
