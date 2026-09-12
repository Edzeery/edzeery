<div x-show="step === 6" x-transition.opacity>
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <div class="edz-card">
                <div class="edz-card__header">
                    <div>
                        <h2 class="edz-card__title">{{ __('products.review_section') }}</h2>
                        <p class="text-sm text-ink-400">{{ __('products.review_section_desc') }}</p>
                    </div>
                </div>
                <div class="edz-card__body space-y-3 text-sm">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted">{{ __('products.product_name_label') }}</p>
                            <p class="mt-0.5 text-ink">{{ $name ?: '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted">{{ __('products.slug_label') }}</p>
                            <p class="mt-0.5 text-ink">{{ $slug ?: '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted">{{ __('products.brand_label') }}</p>
                            <p class="mt-0.5 text-ink">{{ $this->brands[$brand_id] ?? __('products.no_brand') }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted">{{ __('products.categories_label') }}</p>
                            <p class="mt-0.5 text-ink">{{ count($categories) ? count($categories).' selected' : '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted">{{ __('products.price_label') }}</p>
                            <p class="mt-0.5 text-ink">{{ $price !== null ? number_format((float) $price, 2) : '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted">{{ __('products.status_label') }}</p>
                            <p class="mt-0.5 text-ink">{{ $is_active ? __('products.active_label') : '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted">{{ __('products.options_label') }}</p>
                            <p class="mt-0.5 text-ink">{{ $has_variants ? count($variants_preview).' variants' : __('products.simple_product') }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted">{{ __('products.images_label') }}</p>
                            <p class="mt-0.5 text-ink">{{ count($images) }} {{ __('products.images_label') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="edz-card">
                <div class="edz-card__header">
                    <h2 class="edz-card__title">{{ __('products.search_engine') }}</h2>
                </div>
                <div class="edz-card__body grid grid-cols-1 gap-4">
                    <div class="edz-field">
                        <label class="edz-field__label" for="product-meta-title">{{ __('products.meta_title') }}</label>
                        <input id="product-meta-title" type="text" class="edz-input" wire:model="meta_title">
                    </div>
                    <div class="edz-field">
                        <label class="edz-field__label" for="product-meta-description">{{ __('products.meta_description') }}</label>
                        <textarea id="product-meta-description" class="edz-textarea" wire:model="meta_description"
                                  rows="3"></textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>