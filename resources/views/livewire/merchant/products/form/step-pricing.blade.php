<div x-show="step === 2" x-transition.opacity>
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <div class="edz-card">
                <div class="edz-card__header">
                    <div>
                        <h2 class="edz-card__title">{{ __('products.pricing_stock') }}</h2>
                        <p class="text-sm text-ink-400">{{ __('products.pricing_stock_hint') }}</p>
                    </div>
                </div>
                <div class="edz-card__body">
                    <div class="mb-4">
                        <label class="flex items-center gap-2 text-sm font-medium text-ink">
                            <input type="checkbox" wire:model.live="has_variants" class="h-4 w-4 rounded border-surface-border text-brand-600">
                            {{ __('products.has_variants') }}
                        </label>
                        <p class="mt-1 text-xs text-ink-muted">{{ __('products.product_type_hint') }}</p>
                    </div>

                    @if (! $has_variants)
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            <div class="edz-field">
                                <label class="edz-field__label" for="product-price">{{ __('products.price') }}</label>
                                <input id="product-price" type="number" step="0.01" min="0" class="edz-input @error('price') edz-input--error @enderror"
                                       wire:model="price" placeholder="0.00">
                                @error('price') <span class="edz-field__error">{{ $message }}</span> @enderror
                            </div>
                            <div class="edz-field">
                                <label class="edz-field__label" for="product-compare-price">{{ __('products.compare_at_price') }}</label>
                                <input id="product-compare-price" type="number" step="0.01" min="0" class="edz-input @error('compare_price') edz-input--error @enderror"
                                       wire:model="compare_price" placeholder="0.00">
                                @error('compare_price') <span class="edz-field__error">{{ $message }}</span> @enderror
                            </div>
                            <div class="edz-field">
                                <label class="edz-field__label" for="product-cost-price">{{ __('products.cost_price') }}</label>
                                <input id="product-cost-price" type="number" step="0.01" min="0" class="edz-input @error('cost_price') edz-input--error @enderror"
                                       wire:model="cost_price" placeholder="0.00">
                                @error('cost_price') <span class="edz-field__error">{{ $message }}</span> @enderror
                            </div>
                            <div class="rounded-lg border border-surface-border bg-surface-secondary/60 p-3 sm:col-span-2 lg:col-span-3">
                                <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted">{{ __('products.profit_margin') }}</p>
                                @php
                                    $sp = $price !== null && $price !== '' ? (float) $price : null;
                                    $sc = $cost_price !== null && $cost_price !== '' ? (float) $cost_price : null;
                                    $sProfit = $sp !== null && $sc !== null ? $sp - $sc : null;
                                    $sMargin = $sProfit !== null && $sp > 0 ? round(($sProfit / $sp) * 100, 1) : null;
                                @endphp
                                <p class="mt-1 text-sm font-semibold text-ink">
                                    {{ $sProfit !== null ? number_format($sProfit, 2) : '—' }}
                                    <span class="text-ink-muted">({{ $sMargin !== null ? $sMargin.'%' : '—' }})</span>
                                </p>
                            </div>
                        </div>
                    @else
                        <div class="rounded-lg border border-surface-border bg-surface-secondary/60 p-4 text-sm text-ink-muted">
                            {{ __('products.add_options_hint') }}
                        </div>
                    @endif

                    {{-- Per-product order limits override the store defaults. --}}
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 mt-1">
                        <div class="edz-field">
                            <label class="edz-field__label" for="product-min-order-qty">{{ __('products.min_order_qty') }}</label>
                            <input id="product-min-order-qty" type="number" min="1" class="edz-input @error('min_order_qty') edz-input--error @enderror"
                                   wire:model="min_order_qty" placeholder="1">
                            <p class="text-xs text-ink-muted mt-1">{{ __('products.min_order_qty_hint') }}</p>
                            @error('min_order_qty') <span class="edz-field__error">{{ $message }}</span> @enderror
                        </div>
                        <div class="edz-field">
                            <label class="edz-field__label" for="product-max-order-qty">{{ __('products.max_order_qty') }}</label>
                            <input id="product-max-order-qty" type="number" min="1" class="edz-input @error('max_order_qty') edz-input--error @enderror"
                                   wire:model="max_order_qty" placeholder="{{ __('products.unlimited') }}">
                            <p class="text-xs text-ink-muted mt-1">{{ __('products.max_order_qty_hint') }}</p>
                            @error('max_order_qty') <span class="edz-field__error">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>