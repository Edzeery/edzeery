{{-- Export/Import dialogs — pure-Alpine x-show toggling.
    Driven from the parent root x-data scope ({ exportOpen, importOpen }) so no $set
    server round-trip fires just to open a modal. Mirrors components/edz/modal.blade.php
    markup/transitions but binds visibility to the shared scope. --}}

<div x-effect="() => { document.body.style.overflow = (exportOpen || importOpen) ? 'hidden' : 'unset' }"></div>

<div x-show="exportOpen" x-cloak @keydown.escape.window="exportOpen = false"
    class="edz-modal" role="dialog" aria-modal="true" aria-label="{{ __('products.export_title') }}">

    <div class="edz-modal__backdrop"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
    </div>

    <div @click.stop
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95"
        class="edz-modal__panel edz-modal__panel--lg">

        <div>
            <span class="edz-modal__handle"></span>

            <div class="edz-card__header">
                <div>
                    <h2 class="edz-card__title">{{ __('products.export_title') }}</h2>
                    <p class="text-sm text-ink-400">{{ __('products.export_desc') }}</p>
                </div>
                <button type="button" @click="exportOpen = false"
                    class="edz-btn edz-btn--ghost edz-btn--sm shrink-0"
                    aria-label="{{ __('buttons.close') }}"
                    title="{{ __('buttons.close') }}">
                    <x-edz.icon name="x-mark" class="h-4 w-4" />
                </button>
            </div>

            <div class="edz-card__body">
                <div>
                    <p class="edz-label">{{ __('products.export_format') }}</p>
                    <p class="text-xs text-ink-muted">{{ __('products.export_format_hint') }}</p>
                    <div class="mt-2 grid grid-cols-1 gap-2 sm:grid-cols-2">
                        @foreach (['csv', 'excel'] as $format)
                            <label
                                class="flex cursor-pointer items-start gap-3 rounded-lg border border-surface-border p-3 transition hover:bg-surface-secondary has-[:checked]:border-accent-500 has-[:checked]:bg-accent-50">
                                <input type="radio" name="export-format" value="{{ $format }}"
                                    class="mt-0.5 h-4 w-4 shrink-0 accent-accent-600" @checked($loop->first)>
                                <span>
                                    <span
                                        class="block text-sm font-medium text-ink">{{ __('products.export_' . $format) }}</span>
                                    <span
                                        class="block text-xs text-ink-muted">{{ __('products.export_' . $format . '_desc') }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="mt-4">
                    <p class="edz-label">{{ __('products.export_scope') }}</p>
                    <p class="text-xs text-ink-muted">{{ __('products.export_scope_hint') }}</p>
                    <div class="mt-2 grid grid-cols-1 gap-2 sm:grid-cols-3">
                        <label
                            class="flex cursor-pointer items-center gap-3 rounded-lg border border-surface-border p-3 transition hover:bg-surface-secondary has-[:checked]:border-accent-500 has-[:checked]:bg-accent-50">
                            <input type="radio" name="export-scope" value="all"
                                class="h-4 w-4 shrink-0 accent-accent-600" checked>
                            <span class="text-sm font-medium text-ink">{{ __('products.export_all') }}</span>
                        </label>
                        <label
                            class="flex cursor-pointer items-center gap-3 rounded-lg border border-surface-border p-3 transition hover:bg-surface-secondary has-[:checked]:border-accent-500 has-[:checked]:bg-accent-50">
                            <input type="radio" name="export-scope" value="filtered"
                                class="h-4 w-4 shrink-0 accent-accent-600">
                            <span class="text-sm font-medium text-ink">{{ __('products.export_filtered') }}</span>
                        </label>
                        <label
                            class="flex cursor-pointer items-center gap-3 rounded-lg border border-surface-border p-3 transition hover:bg-surface-secondary has-[:checked]:border-accent-500 has-[:checked]:bg-accent-50 {{ empty($selected) ? 'opacity-60' : '' }}">
                            <input type="radio" name="export-scope" value="selected"
                                class="h-4 w-4 shrink-0 accent-accent-600" @disabled(empty($selected))>
                            <span
                                class="text-sm font-medium text-ink">{{ __('products.export_selected', ['count' => count($selected)]) }}</span>
                        </label>
                    </div>
                </div>

                <div class="mt-4 flex items-start gap-2 rounded-lg bg-surface-secondary px-3 py-2.5">
                    <x-edz.icon name="clock" class="mt-0.5 h-4 w-4 shrink-0 text-ink-muted" />
                    <p class="text-sm text-ink-soft">{{ __('products.export_coming_soon') }}</p>
                </div>
            </div>

            <div class="edz-card__footer flex flex-wrap items-center justify-end gap-2">
                <button type="button" @click="exportOpen = false"
                    class="edz-btn edz-btn--ghost edz-btn--sm">
                    {{ __('buttons.cancel') }}
                </button>
                <button type="button" disabled class="edz-btn edz-btn--primary edz-btn--sm">
                    <x-edz.icon name="download" class="h-4 w-4" />
                    {{ __('products.export') }}
                </button>
            </div>
        </div>
    </div>
</div>

<div x-show="importOpen" x-cloak @keydown.escape.window="importOpen = false"
    class="edz-modal" role="dialog" aria-modal="true" aria-label="{{ __('products.import_title') }}">

    <div class="edz-modal__backdrop"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
    </div>

    <div @click.stop
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95"
        class="edz-modal__panel edz-modal__panel--lg">

        <div>
            <span class="edz-modal__handle"></span>

            <div class="edz-card__header">
                <div>
                    <h2 class="edz-card__title">{{ __('products.import_title') }}</h2>
                    <p class="text-sm text-ink-400">{{ __('products.import_desc') }}</p>
                </div>
                <button type="button" @click="importOpen = false"
                    class="edz-btn edz-btn--ghost edz-btn--sm shrink-0"
                    aria-label="{{ __('buttons.close') }}"
                    title="{{ __('buttons.close') }}">
                    <x-edz.icon name="x-mark" class="h-4 w-4" />
                </button>
            </div>

            <div class="edz-card__body">
                <div x-data="{ dragging: false }"
                    class="flex flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed px-6 py-10 text-center transition"
                    :class="dragging ? 'border-accent-500 bg-accent-50' : 'border-surface-border'"
                    @dragover.prevent="dragging = true"
                    @dragleave.prevent="dragging = false"
                    @drop.prevent="dragging = false">
                    <x-edz.icon name="upload" class="h-8 w-8 text-ink-muted" />
                    <p class="text-sm font-medium text-ink">{{ __('products.import_choose_file') }}</p>
                    <p class="text-xs text-ink-muted">{{ __('products.import_dropzone_hint') }}</p>
                    <button type="button" disabled class="edz-btn edz-btn--secondary edz-btn--sm mt-3">
                        {{ __('products.import_browse') }}
                    </button>
                </div>

                <div class="mt-4 flex flex-col gap-2 sm:flex-row sm:items-center">
                    <button type="button" disabled class="edz-btn edz-btn--secondary edz-btn--sm">
                        <x-edz.icon name="document-text" class="h-4 w-4" />
                        {{ __('products.import_template') }}
                    </button>
                    <p class="text-xs text-ink-muted">{{ __('products.import_template_hint') }}</p>
                </div>

                <div class="mt-4 flex items-start gap-2 rounded-lg bg-surface-secondary px-3 py-2.5">
                    <x-edz.icon name="clock" class="mt-0.5 h-4 w-4 shrink-0 text-ink-muted" />
                    <p class="text-sm text-ink-soft">{{ __('products.import_coming_soon') }}</p>
                </div>
            </div>

            <div class="edz-card__footer flex flex-wrap items-center justify-end gap-2">
                <button type="button" @click="importOpen = false"
                    class="edz-btn edz-btn--ghost edz-btn--sm">
                    {{ __('buttons.cancel') }}
                </button>
                <button type="button" disabled class="edz-btn edz-btn--primary edz-btn--sm">
                    <x-edz.icon name="upload" class="h-4 w-4" />
                    {{ __('products.import') }}
                </button>
            </div>
        </div>
    </div>
</div>