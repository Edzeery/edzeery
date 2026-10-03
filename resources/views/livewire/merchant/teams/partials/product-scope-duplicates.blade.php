{{-- Backfill review banner (root-cause fix): rows that exist BOTH as a visibility
     scope and as a confirmation specialist row for the same manager+product.
     Intent is ambiguous by construction, so the owner decides per row here.
     Rendered from product-scope-modal.blade.php. --}}
@if ($this->duplicatedSpecialist)
    <div class="mt-4 rounded-lg border border-warning-200 bg-warning-50/60 p-3"
        role="status"
        wire:key="product-scope-duplicates-{{ $this->membership->id }}">
        <p class="text-sm font-semibold text-warning-800">{{ __('teams.product_scope_duplicate_title') }}</p>
        <p class="mt-1 text-xs leading-relaxed text-warning-700">{{ __('teams.product_scope_duplicate_hint') }}</p>

        <ul class="mt-3 space-y-2">
            @foreach ($this->duplicatedSpecialist as $item)
                <li class="flex items-center justify-between gap-3 rounded-md bg-surface px-3 py-2">
                    <span class="min-w-0">
                        <span class="block truncate text-sm font-medium text-ink">{{ $item['name'] }}</span>
                        @if (! empty($item['sku']))
                            <span class="mt-0.5 block text-xs text-ink-muted">{{ $item['sku'] }}</span>
                        @endif
                    </span>
                    <button type="button"
                        class="edz-btn edz-btn--ghost edz-btn--sm shrink-0 text-warning-800 hover:text-warning-900"
                        x-data
                        data-confirm-title="{{ __('teams.product_scope_duplicate_remove') }}"
                        data-confirm-text="{{ __('teams.product_scope_duplicate_remove_confirm') }}"
                        data-duplicate-product-id="{{ $item['id'] }}"
                        @click.prevent="(async () => { if (await EdzSwal.confirmAction($el.dataset.confirmTitle, $el.dataset.confirmText)) await $wire.removeSpecialistDuplicate($el.dataset.duplicateProductId) })()">
                        {{ __('teams.product_scope_duplicate_remove') }}
                    </button>
                </li>
            @endforeach
        </ul>
    </div>
@endif
