<div x-data="orderProductPicker()">
    @if ($showCreateModal || $showEditModal)
        <x-edz.modal :isOpen="true" :showCloseButton="false" :preventClose="true" size="lg" class="  edz-scroll"
            wire:key="order-create-edit-{{ $showCreateModal ? 'create' : 'edit' }}-{{ $showEditModal ? $editingOrderId : 'new' }}">
            <form wire:submit="{{ $showEditModal ? 'submitEdit' : 'submitCreate' }}">
                <div class="p-6 space-y-5">
                    {{-- Header --}}
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-ink">
                            {{ $showEditModal ? __('merchant_panel.edit_order') : __('merchant_panel.new_order') }}
                        </h3>
                        <div class="flex items-center gap-2">
                            <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm"
                                wire:click="{{ $showEditModal ? 'set(\'showEditModal\', false)' : 'set(\'showCreateModal\', false)' }}">
                                <x-edz.icon name="x-mark" class="w-5 h-5" />
                            </button>
                        </div>
                    </div>

                    {{-- Customer --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="edz-label">{{ __('merchant_panel.name') }} *</label>
                            <input type="text" wire:model="form.customer_name" class="edz-input text-sm" required>
                            @error('form.customer_name')
                                <span class="text-danger-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="edz-label">{{ __('merchant_panel.phone') }} *</label>
                            <input type="tel" wire:model="form.customer_phone" class="edz-input text-sm" required>
                            @error('form.customer_phone')
                                <span class="text-danger-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="edz-label">{{ __('merchant_panel.phone_secondary') }}</label>
                            <input type="tel" wire:model="form.phone_secondary" class="edz-input text-sm">
                        </div>
                    </div>

                    {{-- Delivery — Carrier-first: company → delivery type → office --}}
                    <div x-data="{ delivery: $wire.form.delivery_type }"
                        x-init="$watch('delivery', v => $wire.set('form.delivery_type', v))"
                        x-effect="delivery = $wire.form.delivery_type">
                        <label class="edz-label">{{ __('merchant_panel.delivery') }}</label>
                        <div class="inline-flex rounded-lg border border-surface-border overflow-hidden">
                            <button type="button"
                                :class="delivery === 'home' ? 'bg-brand-500 text-white' : 'bg-surface text-ink'"
                                @click="delivery = 'home'"
                                class="px-4 py-2 text-sm font-medium transition-colors">
                                <x-edz.icon name="home" class="w-4 h-4 inline mr-1" />
                                {{ __('merchant_panel.home_delivery_label') }}
                            </button>
                            <button type="button"
                                :class="delivery === 'stopdesk' ? 'bg-brand-500 text-white' : 'bg-surface text-ink'"
                                @click="delivery = 'stopdesk'"
                                class="px-4 py-2 text-sm font-medium transition-colors">
                                <x-edz.icon name="building-storefront" class="w-4 h-4 inline mr-1" />
                                {{ __('merchant_panel.stop_desk_label') }}
                            </button>
                        </div>

                        {{-- Carrier + office (office deliveries only) --}}
                        <div x-show="delivery === 'stopdesk'" x-cloak class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="edz-label">{{ __('merchant_panel.shipping_company') }}</label>
                                <x-edz.select wire:model="form.shipping_provider_id"
                                    wire:change="loadFormOffices($event.target.value)"
                                    :options="$this->allProviders" option-value="id" option-label="name"
                                    placeholder="{{ __('merchant_panel.select_company') }}" size="sm"
                                    :disabled="$loadingOffices" />
                                @error('form.shipping_provider_id')
                                    <span class="text-danger-500 text-xs mt-1">{{ $message }}</span>
                                @enderror
                            </div>
                            <div x-show="$wire.form.shipping_provider_id" x-cloak>
                                <div class="flex items-center gap-2">
                                    <div class="flex-1">
                                        <label class="edz-label">{{ __('merchant_panel.office') }}</label>
                                        <x-edz.select wire:model="form.stopdesk_point_id"
                                            :options="$this->formOffices" option-value="value"
                                            option-label="label" option-hint="hint"
                                            placeholder="{{ __('merchant_panel.select_office') }}" size="sm"
                                            :disabled="$loadingOffices" />
                                    </div>
                                    <button type="button" wire:click="refreshFormOffices"
                                        wire:loading.attr="disabled" :disabled="$loadingOffices"
                                        class="edz-btn edz-btn--ghost edz-btn--sm mt-5 shrink-0"
                                        aria-label="{{ __('merchant_panel.refresh_offices') }}">
                                        <x-edz.spinner wire:target="refreshFormOffices" class="w-4 h-4" />
                                        <x-edz.icon name="arrow-path" class="w-4 h-4"
                                            wire:loading.remove wire:target="refreshFormOffices" />
                                    </button>
                                </div>
                                <p class="text-xs text-ink-muted mt-1">{{ __('merchant_panel.office_hint') }}</p>
                                @error('form.stopdesk_point_id')
                                    <span class="text-danger-500 text-xs mt-1">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Destination --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="edz-label">{{ __('merchant_panel.state') }}</label>
                            <x-edz.select wire:model="form.state_id" wire:change="loadCities($event.target.value)"
                                :options="$this->allStates" option-value="id" option-label="name" placeholder="—"
                                size="sm" />
                            @error('form.state_id')
                                <span class="text-danger-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="edz-label">{{ __('merchant_panel.city') }}</label>
                            <x-edz.select wire:model="form.city_id" :options="$this->allCities" option-value="id"
                                option-label="name" placeholder="—" size="sm" />
                            @error('form.city_id')
                                <span class="text-danger-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label class="edz-label">{{ __('merchant_panel.address') }}</label>
                            <input type="text" wire:model="form.address" class="edz-input text-sm">
                        </div>
                    </div>

                    {{-- Order Info --}}
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="edz-label">{{ __('merchant_panel.shipment') }}</label>
                            <x-edz.select wire:model="form.shipment_type" :options="[
                                ['value' => 'delivery', 'label' => __('merchant_panel.delivery')],
                                ['value' => 'exchange', 'label' => __('merchant_panel.exchange_label')],
                                ['value' => 'pickup', 'label' => __('merchant_panel.pickup_label')],
                            ]" size="sm" />
                        </div>
                        <div>
                            <label class="edz-label">{{ __('merchant_panel.payment_method') }}</label>
                            <x-edz.select wire:model="form.payment_method" :options="[['value' => 'cod', 'label' => __('merchant_panel.cod')]]" size="sm" />
                        </div>
                        <div>
                            <label class="edz-label">{{ __('merchant_panel.weight_kg') }}</label>
                            <input type="number" wire:model="form.weight_kg" step="0.01" class="edz-input text-sm">
                        </div>
                    </div>

                    {{-- Products --}}
                    <div>
                        <label class="edz-label">{{ __('merchant_panel.products') }}</label>

                        {{-- Trigger to open product picker modal --}}
                        <button type="button" @click="openProductPicker()" :disabled="isLoadingProducts"
                            class="w-full flex items-center gap-3 px-4 py-3 bg-surface-secondary
                                border border-dashed border-surface-border rounded-xl
                                hover:border-brand-400 hover:bg-brand-50
                                transition-colors text-sm text-ink-muted group disabled:opacity-50">
                            <x-edz.spinner :show="'isLoadingProducts'" class="w-5 h-5 text-brand-500" />
                            <x-edz.icon name="qr-code" x-show="!isLoadingProducts"
                                class="w-5 h-5 text-ink-muted group-hover:text-brand-500 transition-colors" />
                            <span class="flex-1 text-start">{{ __('merchant_panel.search_products_barcode') }}</span>
                            <x-edz.icon name="plus"
                                class="w-4 h-4 text-ink-muted group-hover:text-brand-500 transition-colors" />
                        </button>

                        {{-- Items list --}}
                        @if (!empty($form['items']))
                            <div class="mt-3 space-y-2  overflow-y-auto max-h-[calc(80vh-475px)]  edz-scroll">
                                @foreach ($form['items'] as $idx => $item)
                                    <div
                                        class="flex items-center gap-3 p-3 bg-surface-secondary rounded-lg">
                                        {{-- Image --}}
                                        <img src="{{ $item['image_url'] ?? asset('img/icons/noimg.png') }}"
                                            alt=""
                                            class="w-12 h-12 rounded-lg object-cover bg-surface shrink-0">

                                        {{-- Name + SKU --}}
                                        <div class="flex-1 min-w-0">
                                            <div class="text-sm font-medium text-ink truncate">
                                                {{ $item['name'] }}
                                            </div>
                                            <div class="text-xs text-ink-muted mt-0.5">
                                                SKU: {{ $item['sku'] ?? 'â€”' }}
                                                @if (($item['stock'] ?? 0) <= 0)
                                                    <span
                                                        class="text-danger-500 ml-2">{{ __('merchant_panel.out_of_stock') }}</span>
                                                @elseif (($item['stock'] ?? 0) <= 5)
                                                    <span class="text-warning-500 ml-2">{{ $item['stock'] }}
                                                        {{ __('merchant_panel.left') }}</span>
                                                @endif
                                                @if (($item['preorder'] ?? false))
                                                    <span
                                                        class="text-success-600 ml-2">{{ __('merchant_panel.pre_order') }}</span>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Quantity stepper --}}
                                        @php
                                            $capReached = ($item['cap'] ?? null) !== null && ($item['quantity'] ?? 0) >= $item['cap'];
                                        @endphp
                                        <div
                                            class="flex items-center rounded-lg border border-surface-border overflow-hidden shrink-0">
                                            <button type="button"
                                                wire:click="updateFormItemQty({{ $idx }}, {{ max(1, $item['quantity'] - 1) }})"
                                                :disabled="{{ $item['quantity'] <= 1 ? 'true' : 'false' }}"
                                                class="w-8 h-8 flex items-center justify-center bg-surface
                                                    text-ink-muted hover:bg-surface-secondary
                                                    transition-colors disabled:opacity-30 disabled:cursor-not-allowed
                                                    text-sm font-medium select-none">
                                                &minus;
                                            </button>
                                            <input type="number" value="{{ $item['quantity'] }}"
                                                wire:change="updateFormItemQty({{ $idx }}, parseInt($event.target.value))"
                                                min="1" @if (($item['cap'] ?? null) !== null) max="{{ $item['cap'] }}" @endif
                                                class="w-10 h-8 text-center border-x border-surface-border
                                                    bg-transparent text-sm font-semibold text-ink
                                                    focus:outline-none focus:ring-0
                                                    [appearance:textfield]
                                                    [&::-webkit-outer-spin-button]:appearance-none
                                                    [&::-webkit-inner-spin-button]:appearance-none">
                                            <button type="button"
                                                wire:click="updateFormItemQty({{ $idx }}, {{ $item['quantity'] + 1 }})"
                                                :disabled="{{ $capReached ? 'true' : 'false' }}"
                                                class="w-8 h-8 flex items-center justify-center bg-surface
                                                    text-ink-muted hover:bg-surface-secondary
                                                    transition-colors disabled:opacity-30 disabled:cursor-not-allowed
                                                    text-sm font-medium select-none">
                                                &plus;
                                            </button>
                                        </div>

                                        {{-- Unit price (editable) --}}
                                        <div class="shrink-0 hidden sm:block">
                                            <input type="number" value="{{ $item['price'] }}"
                                                wire:change="updateFormItemPrice({{ $idx }}, parseFloat($event.target.value))"
                                                step="10" min="0"
                                                class="edz-input text-xs w-20 text-center py-1"
                                                placeholder="{{ __('merchant_panel.price') }}">
                                        </div>

                                        {{-- Line total --}}
                                        <div class="text-right shrink-0 w-24">
                                            <div class="text-sm font-bold text-ink tabular-nums">
                                                {{ currency($item['price'] * $item['quantity']) }}</div>
                                            <div class="text-xs text-ink-muted">{{ $item['quantity'] }} أ—
                                                {{ currency($item['price']) }}</div>
                                        </div>

                                        {{-- Delete --}}
                                        <button type="button" wire:click="removeFormItem({{ $idx }})"
                                            class="text-danger-400 hover:text-danger-600 shrink-0 p-1 rounded hover:bg-danger-surface transition-colors">
                                            <x-edz.icon name="x-mark" class="w-4 h-4" />
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Duplicate-detection warning (P28 extended) --}}
                    @if (!empty($formDuplicateWarnings))
                        <div class="rounded-xl border border-warning/40 bg-warning/5 p-3">
                            <div class="flex items-center gap-2 text-warning mb-2">
                                <x-edz.icon name="exclamation-triangle" class="w-4 h-4" />
                                <span class="text-sm font-medium">
                                    {{ __('order_flow.duplicate_detected', ['count' => count($formDuplicateWarnings)]) }}
                                </span>
                            </div>
                            <ul class="space-y-1.5 text-sm">
                                @foreach ($formDuplicateWarnings as $dup)
                                    <li class="flex items-center justify-between gap-2">
                                        <button type="button"
                                            wire:click="set('showCreateModal', false); set('showEditModal', false); openOrderDetails('{{ $dup['order_id'] }}')"
                                            class="flex items-center gap-2 text-ink hover:text-brand-600 truncate">
                                            <span class="text-xs text-ink-muted">{{ __('merchant_panel.status') }}</span>
                                            <span class="text-xs">
                                                {{ \Edzeery\MyStatusKit\Facades\Status::for('order', $dup['status_key'])->label() }}
                                            </span>
                                            <span class="truncate font-medium">#{{ $dup['number'] }}</span>
                                            <span class="text-ink-muted text-xs shrink-0">
                                                {{ \Carbon\Carbon::parse($dup['created_at'])->diffForHumans() }}
                                            </span>
                                        </button>
                                        <span class="shrink-0 text-xs text-ink-muted">
                                            ×{{ $dup['total_overlap_qty'] }}
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Order Summary â€” Horizontal --}}
                    @if (!empty($form['items']))
                        @php
                            $subtotal = collect($form['items'])->sum(fn($i) => $i['price'] * $i['quantity']);
                            $totalWeight = collect($form['items'])->sum(fn($i) => ($i['weight'] ?? 0) * $i['quantity']);
                            $discount = 0;
                            if ($form['discount_type'] && $form['discount_value']) {
                                $discount =
                                    $form['discount_type'] === 'amount'
                                        ? (float) $form['discount_value']
                                        : round(($subtotal * (float) $form['discount_value']) / 100, 2);
                            }
                            $grandTotal = max(0, $subtotal - $discount);
                        @endphp
                        <div class="bg-surface-secondary rounded-lg p-4">
                            {{-- Top row: main stats --}}
                            <div class="flex items-center justify-between gap-4 flex-wrap">
                                <div class="flex items-center gap-4 text-sm">
                                    <span class="text-ink-muted">{{ __('merchant_panel.items') }}:</span>
                                    <span
                                        class="font-semibold text-ink">{{ collect($form['items'])->sum('quantity') }}</span>
                                </div>
                                <div class="flex items-center gap-4 text-sm">
                                    <span class="text-ink-muted">{{ __('merchant_panel.subtotal') }}:</span>
                                    <span class="font-semibold text-ink tabular-nums">{{ currency($subtotal) }}</span>
                                </div>
                                @if ($totalWeight > 0)
                                    <div class="flex items-center gap-4 text-sm">
                                        <span class="text-ink-muted">{{ __('merchant_panel.total_weight') }}:</span>
                                        <span
                                            class="font-medium text-ink tabular-nums">{{ number_format($totalWeight, 2) }}
                                            kg</span>
                                    </div>
                                @endif
                                <div class="flex items-center gap-4 text-sm">
                                    <span class="text-ink-muted">{{ __('merchant_panel.delivery_cost') }}:</span>
                                    <span class="text-success-500 font-medium">{{ __('merchant_panel.free') }}</span>
                                </div>
                            </div>

                            {{-- Discount row --}}
                            <div
                                class="flex items-center justify-between gap-4 mt-3 pt-3 border-t border-surface-border">
                                <div class="flex items-center gap-2">
                                    <x-edz.select wire:model="form.discount_type" :options="[
                                        ['value' => '', 'label' => __('merchant_panel.discount')],
                                        ['value' => 'amount', 'label' => __('merchant_panel.fixed_amount')],
                                        ['value' => 'percent', 'label' => __('merchant_panel.percentage')],
                                    ]" size="sm"
                                        class="w-28" />
                                    @if ($form['discount_type'])
                                        <input type="number" wire:model="form.discount_value"
                                            class="edz-input text-xs py-1 w-20" min="0"
                                            placeholder="{{ $form['discount_type'] === 'percent' ? '%' : 'DZD' }}">
                                    @endif
                                    @if ($form['discount_type'] && $form['discount_value'])
                                        <input type="text" wire:model="form.discount_reason"
                                            class="edz-input text-xs py-1 flex-1 max-w-xs"
                                            placeholder="{{ __('merchant_panel.discount_reason') }}">
                                    @endif
                                </div>
                                <span
                                    class="text-sm font-medium tabular-nums {{ $discount > 0 ? 'text-danger-500' : 'text-ink-muted' }}">
                                    {{ $discount > 0 ? '-' . currency($discount) : 'â€”' }}
                                </span>
                            </div>

                            {{-- Grand total row --}}
                            <div
                                class="flex items-center justify-between mt-3 pt-3 border-t border-surface-border">
                                <span class="text-base font-bold text-ink">{{ __('merchant_panel.total') }}</span>
                                <span
                                    class="text-lg font-bold text-ink tabular-nums">{{ currency($grandTotal) }}</span>
                            </div>
                        </div>
                    @endif

                    {{-- Notes --}}
                    <div>
                        <label class="edz-label">{{ __('merchant_panel.notes') }}</label>
                        <textarea wire:model="form.notes" rows="2" class="edz-input text-sm"></textarea>
                    </div>

                    {{-- Submit --}}
                    <div class="flex justify-end gap-2 pt-2 border-t border-surface-border">
                        <button type="button" class="edz-btn edz-btn--ghost"
                            wire:click="{{ $showEditModal ? 'set(\'showEditModal\', false)' : 'set(\'showCreateModal\', false)' }}">
                            {{ __('buttons.cancel') }}
                        </button>
                        <button type="submit" class="edz-btn edz-btn--primary" wire:loading.attr="disabled"
                            wire:loading.class="opacity-50 pointer-events-none" wire:target="submitCreate,submitEdit">
                            <x-edz.spinner wire:target="submitCreate,submitEdit" />
                            <span wire:loading.remove
                                wire:target="submitCreate,submitEdit">{{ $showEditModal ? __('merchant_panel.update') : __('merchant_panel.create') }}</span>
                            <span
                                class="sr-only">{{ $showEditModal ? __('merchant_panel.update') : __('merchant_panel.create') }}</span>
                        </button>
                    </div>
                </div>
            </form>
        </x-edz.modal>
    @endif

    {{-- Product Picker Modal --}}
    @if ($showProductPickerModal)
        <x-edz.modal :isOpen="true" :showCloseButton="false" :preventClose="true" size="md">
            <div class="flex flex-col max-h-[85vh]">
                {{-- Drag handle (mobile) --}}
                <div class="edz-modal__handle sm:hidden"></div>

                {{-- Header --}}
                <div class="flex items-center justify-between px-5 pt-5 pb-3">
                    <h3 class="text-lg font-bold text-ink">{{ __('merchant_panel.products') }}</h3>
                    <button type="button" @click="open = false; closeProductPicker()" class="edz-modal__close"
                        style="position:static;">
                        <x-edz.icon name="x-mark" class="w-5 h-5" />
                    </button>
                </div>

                {{-- Search --}}
                <div class="px-5 pb-3">
                    <div class="relative">
                        <input type="text" @input="onSearchInput($event)" data-product-search-input
                            placeholder="{{ __('merchant_panel.search_products_barcode') }}"
                            class="edz-input text-sm ps-10 pe-10"
                            @keydown.enter.prevent="selectProductByBarcode($event)">
                        <x-edz.icon name="magnifying-glass"
                            class="w-4 h-4 absolute start-3 top-1/2 -translate-y-1/2 text-ink-muted pointer-events-none" />
                        <x-edz.icon name="qr-code"
                            class="w-4 h-4 absolute end-3 top-1/2 -translate-y-1/2 text-ink-muted pointer-events-none" />
                    </div>
                </div>

                {{-- Counter --}}
                <div class="px-5 pb-2">
                    <span class="text-xs text-ink-muted"
                        x-text="(searchTerm && searchTerm.length >= 2 ? visibleCount : {{ count($formProductResults) }}) + ' {{ __('merchant_panel.products') }}'"></span>
                </div>

                {{-- Product list --}}
                <div class="min-h-0 flex-1  max-h-[calc(100vh-475px)] overflow-y-auto edz-scroll px-5 pb-5">
                    {{-- Skeleton loading (while loadProducts is executing) --}}
                    <div wire:loading wire:target="loadProducts" class="space-y-3 py-2">
                        @foreach (range(1, 5) as $i)
                            <div class="flex items-center gap-3 py-2">
                                <div class="w-11 h-11 rounded-xl edz-skeleton shrink-0"></div>
                                <div class="flex-1 space-y-2">
                                    <x-edz.skeleton width="{{ 40 + $i * 10 }}%" height="0.875rem" />
                                    <x-edz.skeleton width="6rem" height="0.75rem" />
                                </div>
                                <x-edz.skeleton width="3.5rem" height="1rem" />
                            </div>
                        @endforeach
                    </div>

                    {{-- Product items (server-rendered) --}}
                    <div wire:loading.remove wire:target="loadProducts"
                        class="divide-y divide-surface-border">
                        @forelse ($formProductResults as $pv)
                            @php
                                $searchText = mb_strtolower(
                                    $pv['product_name'] . ' ' . ($pv['first_variant']['sku'] ?? ''),
                                );
                            @endphp
                            <div data-search="{{ $searchText }}"
                                x-show="!searchTerm || searchTerm.length < 2 || $el.dataset.search.includes(searchTerm.toLowerCase())"
                                class="transition-opacity">

                                {{-- Multi-variant product --}}
                                @if ($pv['has_variants'] && ($pv['variant_count'] ?? 0) > 1)
                                    <button type="button" @click="openVariants('{{ $pv['product_id'] }}')"
                                        :disabled="isLoadingVariants"
                                        class="w-full text-left py-3 hover:bg-surface-secondary flex items-center gap-3 text-sm transition-colors rounded-lg px-2 -mx-2 disabled:opacity-50">
                                        <img src="{{ $pv['image_url'] ?? asset('img/icons/noimg.png') }}"
                                            alt="" loading="lazy"
                                            class="w-11 h-11 rounded-xl object-cover bg-surface-secondary shrink-0">
                                        <div class="flex-1 min-w-0">
                                            <div class="font-medium text-ink truncate">{{ $pv['product_name'] }}
                                            </div>
                                            <div class="text-xs text-ink-muted mt-0.5">
                                                {{ $pv['variant_count'] }} {{ __('merchant_panel.variants') }}
                                            </div>
                                        </div>
                                        <span
                                            class="text-xs text-ink-muted shrink-0 tabular-nums">{{ $pv['price_range'] }}</span>
                                        <x-edz.spinner :show="'isLoadingVariants'" class="w-4 h-4 text-ink-muted shrink-0" />
                                        <x-edz.icon name="chevron-left" x-show="!isLoadingVariants"
                                            class="w-4 h-4 text-ink-muted shrink-0 rtl:rotate-180" />
                                    </button>

                                    {{-- Single variant product --}}
                                @elseif ($pv['first_variant'])
                                    @php
                                        $isProductSelected = isset($formSelectedItems[$pv['first_variant']['id']]);
                                    @endphp
                                    <button type="button"
                                        @if (!$isProductSelected) @click="selectProduct('{{ $pv['first_variant']['id'] }}')" @endif
                                        :disabled="isAddingProduct"
                                        class="w-full text-left py-3 flex items-center gap-3 text-sm transition-colors rounded-lg px-2 -mx-2
                                    {{ $isProductSelected
                                        ? 'bg-success-surface-subtle border border-success-border'
                                        : 'hover:bg-surface-secondary' }}
                                    disabled:opacity-50">
                                        <img src="{{ $pv['image_url'] ?? asset('img/icons/noimg.png') }}"
                                            alt="" loading="lazy"
                                            class="w-11 h-11 rounded-xl object-cover bg-surface-secondary shrink-0">
                                        <div class="flex-1 min-w-0">
                                            <div class="font-medium text-ink truncate">{{ $pv['product_name'] }}
                                            </div>
                                            <div class="text-xs text-ink-muted mt-0.5 flex items-center gap-1.5">
                                                <span>SKU: {{ $pv['first_variant']['sku'] ?? 'â€”' }}</span>
                                                @if (($pv['first_variant']['stock_status'] ?? '') === 'out')
                                                    <span
                                                        class="text-danger-500 font-medium">{{ __('merchant_panel.out_of_stock') }}</span>
                                                @elseif (($pv['first_variant']['stock_status'] ?? '') === 'low')
                                                    <span
                                                        class="text-warning-500 font-medium">{{ $pv['first_variant']['stock_text'] }}</span>
                                                @else
                                                    <span
                                                        class="text-success-500 font-medium">{{ $pv['first_variant']['stock_text'] }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <span
                                            class="text-ink font-semibold shrink-0 tabular-nums">{{ $pv['first_variant']['price_formatted'] }}</span>
                                        @if ($isProductSelected)
                                            <span
                                                class="w-8 h-8 flex items-center justify-center rounded-lg bg-success-surface text-success-fg shrink-0">
                                                <x-edz.icon name="check" class="w-4 h-4" />
                                            </span>
                                        @else
                                            <span
                                                class="w-8 h-8 flex items-center justify-center rounded-lg bg-brand-surface text-brand-fg shrink-0">
                                                <svg x-show="!isAddingProduct" class="w-4 h-4" fill="none"
                                                    viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M12 4.5v15m7.5-7.5h-15" />
                                                </svg>
                                                <x-edz.spinner :show="'isAddingProduct'" />
                                            </span>
                                        @endif
                                    </button>
                                @endif
                            </div>
                        @empty
                            <div class="px-4 py-10 text-center">
                                <x-edz.icon name="magnifying-glass"
                                    class="w-10 h-10 text-ink-muted/40 mx-auto mb-3" />
                                <p class="text-sm text-ink-muted">{{ __('merchant_panel.no_products_found') }}
                                </p>
                            </div>
                        @endforelse

                        {{-- No search results (all items hidden by x-show) --}}
                        <div x-show="searchTerm && searchTerm.length >= 2 && visibleCount === 0"
                            class="px-4 py-10 text-center">
                            <x-edz.icon name="magnifying-glass" class="w-10 h-10 text-ink-muted/40 mx-auto mb-3" />
                            <p class="text-sm text-ink-muted">{{ __('merchant_panel.no_products_found') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </x-edz.modal>
    @endif

    {{-- Variant Picker Modal --}}
    @if ($showVariantPickerModal)
        <x-edz.modal :isOpen="true" :showCloseButton="false" :preventClose="true" size="sm">
            <div class="flex flex-col max-h-[85vh]">
                {{-- Drag handle (mobile) --}}
                <div class="edz-modal__handle sm:hidden"></div>

                {{-- Header --}}
                <div class="flex items-center justify-between px-5 pt-5 pb-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <button type="button"
                            @click="open = false; closeVariantPicker(); $wire.set('showProductPickerModal', true);"
                            class="edz-btn edz-btn--ghost edz-btn--sm shrink-0">
                            <x-edz.icon name="arrow-right" class="w-4 h-4 rtl:rotate-180" />
                            <span class="hidden sm:inline">{{ __('buttons.back') }}</span>
                        </button>
                        @if ($formSelectedProduct)
                            <div class="flex items-center gap-2 min-w-0">
                                <img src="{{ $formSelectedProduct['image_url'] ?? asset('img/icons/noimg.png') }}"
                                    alt="" class="w-8 h-8 rounded-lg object-cover bg-surface shrink-0">
                                <span
                                    class="text-sm font-bold text-ink truncate">{{ $formSelectedProduct['name'] }}</span>
                            </div>
                        @endif
                    </div>
                    <button type="button" @click="open = false; closeVariantPicker()" class="edz-modal__close"
                        style="position:static;">
                        <x-edz.icon name="x-mark" class="w-5 h-5" />
                    </button>
                </div>

                {{-- Variant list --}}
                <div class="px-5 pb-3">
                    <div class="relative">
                        <input type="text" @input="onVariantSearchInput($event)" data-variant-search-input
                            placeholder="{{ __('merchant_panel.search_variants') }}"
                            class="edz-input text-sm ps-10 pe-10">
                        <x-edz.icon name="magnifying-glass"
                            class="w-4 h-4 absolute start-3 top-1/2 -translate-y-1/2 text-ink-muted pointer-events-none" />
                    </div>
                </div>

                {{-- Variant list --}}
                <div class="flex-1 overflow-y-auto px-5 pb-5 max-h-[calc(100vh-475px)]  edz-scroll">
                    @if ($formSelectedProduct && count($formSelectedProduct['variants']) > 0)
                        <div class="divide-y divide-surface-border">
                            @foreach ($formSelectedProduct['variants'] as $variant)
                                @php
                                    $isVariantSelected = isset($formSelectedItems[$variant['id']]);
                                    $variantQty = $formSelectedItems[$variant['id']] ?? 0;
                                    $isDisabled = !$variant['is_active'] || $variant['stock'] <= 0;
                                    $variantSearchText = mb_strtolower(
                                        $variant['name'] .
                                            ' ' .
                                            ($variant['sku'] ?? '') .
                                            ' ' .
                                            ($variant['option_labels'] ?? ''),
                                    );
                                @endphp
                                <div data-variant-search="{{ $variantSearchText }}"
                                    x-show="!variantQuery || variantQuery.length < 2 || $el.dataset.variantSearch.includes(variantQuery.toLowerCase())"
                                    class="py-3 flex items-center gap-3 text-sm rounded-lg px-2 -mx-2 transition-colors
                                {{ $isVariantSelected ? 'bg-success-surface-subtle' : '' }}
                                {{ $isDisabled && !$isVariantSelected ? 'opacity-40' : '' }}">
                                    <div class="flex-1 min-w-0">
                                        <div class="font-medium text-ink text-xs truncate">{{ $variant['name'] }}
                                        </div>
                                        <div
                                            class="text-[11px] text-ink-muted mt-0.5 flex items-center gap-1.5 flex-wrap">
                                            @if ($variant['option_labels'])
                                                <span>{{ $variant['option_labels'] }}</span>
                                                <span class="text-surface-border">آ·</span>
                                            @endif
                                            <span>SKU: {{ $variant['sku'] ?? 'â€”' }}</span>
                                            @if ($isVariantSelected)
                                                <span class="text-success-fg font-medium">آ·
                                                    {{ $variantQty }}
                                                    {{ __('merchant_panel.in_cart') }}</span>
                                            @elseif ($variant['stock'] <= 0)
                                                <span
                                                    class="text-danger-500 font-medium">{{ __('merchant_panel.out_of_stock') }}</span>
                                            @elseif ($variant['stock'] <= 5)
                                                <span class="text-warning-500 font-medium">{{ $variant['stock'] }}
                                                    {{ __('merchant_panel.left') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <span
                                        class="text-ink font-semibold text-xs shrink-0 tabular-nums">{{ currency($variant['price']) }}</span>
                                    @if ($isVariantSelected)
                                        <span
                                            class="w-8 h-8 flex items-center justify-center rounded-lg bg-success-surface
                                        text-success-fg shrink-0">
                                            <x-edz.icon name="check" class="w-4 h-4" />
                                        </span>
                                    @elseif ($variant['is_active'] && $variant['stock'] > 0)
                                        <button type="button" @click="selectVariant('{{ $variant['id'] }}')"
                                            :disabled="isAddingProduct"
                                            class="w-8 h-8 flex items-center justify-center rounded-lg bg-brand-surface
                                            text-brand-fg hover:bg-brand-surface
                                            transition-colors shrink-0 disabled:opacity-50">
                                            <svg x-show="!isAddingProduct" class="w-4 h-4" fill="none"
                                                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M12 4.5v15m7.5-7.5h-15" />
                                            </svg>
                                            <x-edz.spinner :show="'isAddingProduct'" />
                                        </button>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        {{-- No variant search results --}}
                        <div x-show="variantQuery && variantQuery.length >= 2 && variantVisibleCount === 0"
                            class="px-4 py-10 text-center">
                            <x-edz.icon name="magnifying-glass" class="w-10 h-10 text-ink-muted/40 mx-auto mb-3" />
                            <p class="text-sm text-ink-muted">{{ __('merchant_panel.no_products_found') }}</p>
                        </div>
                    @else
                        <div class="px-4 py-10 text-center">
                            <x-edz.icon name="cube" class="w-10 h-10 text-ink-muted/40 mx-auto mb-3" />
                            <p class="text-sm text-ink-muted">{{ __('merchant_panel.no_products_found') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </x-edz.modal>
    @endif
</div>
