<div x-show="step === 4" x-transition.opacity>
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            @if ($has_variants)
                <div class="edz-card">
                    <div class="edz-card__header">
                        <div>
                            <h2 class="edz-card__title">{{ __('products.options') }}</h2>
                            <p class="text-sm text-ink-400">{{ __('products.options_hint') }}</p>
                        </div>
                        <button type="button" wire:click="addOption" wire:loading.attr="disabled" class="edz-btn edz-btn--secondary edz-btn--sm">{{ __('products.add_option') }}</button>
                    </div>
                    <div class="edz-card__body space-y-4">
                        @forelse ($options as $index => $option)
                            <div class="grid grid-cols-1 gap-3 rounded-lg border border-surface-border p-4 md:grid-cols-2">
                                <div class="edz-field">
                                    <label class="edz-field__label" for="option-{{ $index }}">{{ __('products.option') }}</label>
                                    <select id="option-{{ $index }}" class="edz-select"
                                            wire:change="optionChanged({{ $index }}, $event.target.value)">
                                        <option value="">{{ __('products.select_option') }}</option>
                                        @foreach ($this->productOptions as $opt)
                                            <option value="{{ $opt->id }}"
                                                    @selected(($option['product_option_id'] ?? null) === $opt->id)>
                                                {{ $opt->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="edz-field">
                                    <label class="edz-field__label" for="option-values-{{ $index }}">{{ __('products.values') }}</label>
                                    @if (($option['type'] ?? null) === \App\Enums\Store\ProductOptionInputType::TEXT->value)
                                        <div class="rounded-md border border-surface-border px-3 py-2 text-sm text-ink-muted">
                                            {{ __('products.text_options_hint') }}
                                        </div>
                                    @elseif (! empty($option['product_option_id']))
                                        <select id="option-values-{{ $index }}" class="edz-select" multiple size="3"
                                                wire:model="options.{{ $index }}.values"
                                                wire:change="valuesChanged({{ $index }})">
                                            @foreach ($this->optionValuesByOption->get($option['product_option_id'], collect()) as $value)
                                                <option value="{{ $value->id }}"
                                                        @selected(in_array($value->id, $option['values'] ?? []))>
                                                    {{ $value->value }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @else
                                        <div class="rounded-md border border-surface-border px-3 py-2 text-sm text-ink-muted">
                                            {{ __('products.select_option_to_configure') }}
                                        </div>
                                    @endif
                                </div>

                                <div class="md:col-span-2">
                                    <button type="button" wire:click="removeOption({{ $index }})"
                                            class="text-sm font-semibold text-danger-600 hover:text-danger-700">
                                        {{ __('products.remove_option') }}
                                    </button>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-ink-muted">{{ __('products.no_options_yet') }}</p>
                        @endforelse
                    </div>
                </div>

                <div class="edz-card">
                    <div class="edz-card__header">
                        <div>
                            <h2 class="edz-card__title">{{ __('products.variants') }}</h2>
                            <p class="text-sm text-ink-400">{{ __('products.variants_hint') }}</p>
                        </div>
                    </div>
                    <div class="edz-card__body">
                        @if (count($variants_preview) === 0)
                            <p class="text-sm text-ink-muted">{{ __('products.add_options_hint') }}</p>
                        @else
                            <div class="mb-4 grid grid-cols-1 gap-3 rounded-lg border border-surface-border bg-surface-secondary/50 p-4 sm:grid-cols-2 lg:grid-cols-5">
                                <div class="edz-field">
                                    <label class="edz-field__label" for="apply-price">{{ __('products.price') }}</label>
                                    <input id="apply-price" type="number" step="0.01" min="0" class="edz-input"
                                           wire:model="apply_all_price" placeholder="0.00">
                                </div>
                                <div class="edz-field">
                                    <label class="edz-field__label" for="apply-cost">{{ __('products.cost') }}</label>
                                    <input id="apply-cost" type="number" step="0.01" min="0" class="edz-input"
                                           wire:model="apply_all_cost_price" placeholder="0.00">
                                </div>
                                <div class="edz-field">
                                    <label class="edz-field__label" for="apply-stock">{{ __('products.stock') }}</label>
                                    <input id="apply-stock" type="number" min="0" class="edz-input"
                                           wire:model="apply_all_stock" placeholder="0">
                                </div>
                                <div class="edz-field">
                                    <label class="edz-field__label" for="apply-low-stock">{{ __('products.low_stock') }}</label>
                                    <input id="apply-low-stock" type="number" min="0" class="edz-input"
                                           wire:model="apply_all_low_stock" placeholder="5">
                                </div>
                                <div class="flex items-end">
                                    <button type="button" wire:click="applyAll" wire:loading.attr="disabled" class="edz-btn edz-btn--secondary w-full">{{ __('products.apply_to_all') }}</button>
                                </div>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="border-b border-surface-border text-start text-xs uppercase tracking-wider text-ink-muted">
                                            <th class="px-3 py-2 text-start font-semibold">{{ __('products.variant') }}</th>
                                            <th class="px-3 py-2 text-start font-semibold">{{ __('products.price') }}</th>
                                            <th class="px-3 py-2 text-start font-semibold">{{ __('products.cost') }}</th>
                                            <th class="px-3 py-2 text-start font-semibold">{{ __('products.compare') }}</th>
                                            <th class="px-3 py-2 text-start font-semibold">{{ __('products.stock') }}</th>
                                            <th class="px-3 py-2 text-start font-semibold">{{ __('products.low_stock') }}</th>
                                            <th class="px-3 py-2 text-start font-semibold">{{ __('products.profit') }}</th>
                                            <th class="px-3 py-2 text-start font-semibold">{{ __('products.margin') }}</th>
                                            <th class="px-3 py-2 text-start font-semibold">{{ __('products.active') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($variants_preview as $index => $variant)
                                            <tr class="border-b border-surface-border last:border-0">
                                                <td class="max-w-48 px-3 py-2 align-top text-xs font-medium text-ink-soft">
                                                    {{ $variant['labels'] ?? $variant['name'] ?? '—' }}
                                                </td>
                                                <td class="px-3 py-2">
                                                    <input type="number" step="0.01" min="0" class="edz-input min-w-24 px-2 py-1 text-xs"
                                                           wire:model.blur="variants_preview.{{ $index }}.price">
                                                </td>
                                                <td class="px-3 py-2">
                                                    <input type="number" step="0.01" min="0" class="edz-input min-w-24 px-2 py-1 text-xs"
                                                           wire:model.blur="variants_preview.{{ $index }}.cost_price">
                                                </td>
                                                <td class="px-3 py-2">
                                                    <input type="number" step="0.01" min="0" class="edz-input min-w-24 px-2 py-1 text-xs"
                                                           wire:model.blur="variants_preview.{{ $index }}.compare_price">
                                                </td>
                                                <td class="px-3 py-2">
                                                    <input type="number" min="0" class="edz-input min-w-20 px-2 py-1 text-xs"
                                                           wire:model.blur="variants_preview.{{ $index }}.stock">
                                                </td>
                                                <td class="px-3 py-2">
                                                    <input type="number" min="0" class="edz-input min-w-20 px-2 py-1 text-xs"
                                                           wire:model.blur="variants_preview.{{ $index }}.low_stock_threshold">
                                                </td>
                                                @php
                                                    $vp = $variant['price'] ?? null;
                                                    $vc = $variant['cost_price'] ?? null;
                                                    $vProfit = ($vp !== null && $vp !== '' && $vc !== null && $vc !== '')
                                                        ? (float) $vp - (float) $vc
                                                        : null;
                                                    $vMargin = $vProfit !== null && (float) $vp > 0
                                                        ? round(($vProfit / (float) $vp) * 100, 1)
                                                        : null;
                                                @endphp
                                                <td class="px-3 py-2 whitespace-nowrap text-xs text-ink-soft">
                                                    {{ $vProfit !== null ? number_format($vProfit, 2) : '—' }}
                                                </td>
                                                <td class="px-3 py-2 whitespace-nowrap text-xs text-ink-soft">
                                                    {{ $vMargin !== null ? $vMargin.'%' : '—' }}
                                                </td>
                                                <td class="px-3 py-2">
                                                    <input type="checkbox" class="h-4 w-4 rounded border-surface-border text-brand-600"
                                                           wire:model="variants_preview.{{ $index }}.is_active">
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <div class="edz-card">
                    <div class="edz-card__body">
                        <div class="rounded-lg border border-surface-border bg-surface-secondary/60 p-8 text-center">
                            <p class="text-sm text-ink-muted">{{ __('products.add_options_hint') }}</p>
                            <p class="mt-1 text-xs text-ink-muted">{{ __('products.product_type_hint') }}</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>