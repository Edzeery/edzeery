<div x-show="step === 5" x-transition.opacity>
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            @if (! $has_variants)
                <div class="edz-card">
                    <div class="edz-card__header">
                        <div>
                            <h2 class="edz-card__title">{{ __('products.pricing_stock') }}</h2>
                            <p class="text-sm text-ink-400">{{ __('products.pricing_stock_hint') }}</p>
                        </div>
                    </div>
                    <div class="edz-card__body grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="edz-field">
                        <label class="edz-field__label" for="product-stock">{{ __('products.stock') }}</label>
                        <input id="product-stock" type="number" min="0" class="edz-input @error('stock') edz-input--error @enderror"
                               wire:model="stock" placeholder="0">
                        @error('stock') <span class="edz-field__error">{{ $message }}</span> @enderror
                    </div>
                    <div class="edz-field">
                        <label class="edz-field__label" for="product-low-stock">{{ __('products.low_stock_threshold') }}</label>
                        <input id="product-low-stock" type="number" min="0" class="edz-input @error('low_stock_threshold') edz-input--error @enderror"
                               wire:model="low_stock_threshold" placeholder="5">
                        @error('low_stock_threshold') <span class="edz-field__error">{{ $message }}</span> @enderror
                    </div>
                    </div>
                </div>
            @else
                <div class="edz-card">
                    <div class="edz-card__body">
                        <div class="rounded-lg border border-surface-border bg-surface-secondary/60 p-4 text-sm text-ink-muted">
                            {{ __('products.variants_hint') }}
                        </div>
                    </div>
                </div>
            @endif

            <div class="edz-card">
                <div class="edz-card__header">
                    <div>
                        <h2 class="edz-card__title">{{ __('products.codes') }}</h2>
                        <p class="text-sm text-ink-400">{{ __('products.codes_hint') }}</p>
                    </div>
                </div>
                <div class="edz-card__body grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="edz-field">
                        <label class="edz-field__label" for="product-sku">{{ __('products.sku') }}</label>
                        <input id="product-sku" type="text" class="edz-input @error('sku') edz-input--error @enderror"
                               wire:model="sku" @disabled($auto_generate_sku) placeholder="{{ __('products.auto_generated') }}">
                        @error('sku')
                            <span class="edz-field__error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="edz-field">
                        <label class="edz-field__label" for="product-barcode">{{ __('products.barcode') }}</label>
                        <input id="product-barcode" type="text" class="edz-input @error('barcode') edz-input--error @enderror"
                               wire:model="barcode" @disabled($auto_generate_barcode) placeholder="{{ __('products.auto_generated') }}">
                        @error('barcode')
                            <span class="edz-field__error">{{ $message }}</span>
                        @enderror
                    </div>

                    <label class="flex items-center gap-2 text-sm font-medium text-ink">
                        <input type="checkbox" wire:model.live="auto_generate_sku" class="h-4 w-4 rounded border-surface-border text-brand-600">
                        {{ __('products.auto_generate_sku') }}
                    </label>
                    <label class="flex items-center gap-2 text-sm font-medium text-ink">
                        <input type="checkbox" wire:model.live="auto_generate_barcode" class="h-4 w-4 rounded border-surface-border text-brand-600">
                        {{ __('products.auto_generate_barcode') }}
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>