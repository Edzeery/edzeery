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

                    {{-- Customer & Address — 4-col grid (1 @375, 2 @768, 4 @1440) --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 min-[1440px]:grid-cols-4 gap-4">
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
                        <div>
                            <label class="edz-label">{{ __('merchant_panel.address') }}</label>
                            <input type="text" wire:model="form.address" class="edz-input text-sm">
                        </div>
                    </div>

                    {{-- Delivery cascade — company → type → wilaya → city → office --}}
                    <div x-data="{ delivery: $wire.form.delivery_type }"
                        x-init="$watch('delivery', v => $wire.set('form.delivery_type', v))"
                        x-effect="delivery = $wire.form.delivery_type">
                        <label class="edz-label">{{ __('merchant_panel.shipping_partner') }}</label>
                    @include('livewire.merchant.orders.partials.partner-picker', ['picker' => 'form'])

                    <label class="edz-label mt-4">{{ __('merchant_panel.delivery') }}</label>
                        <div class="inline-flex rounded-lg border border-surface-border overflow-hidden">
                            <button type="button"
                                :class="delivery === 'home' ? 'bg-brand-500 text-white' : 'bg-surface text-ink'"
                                @click="delivery = 'home'; $wire.changeDeliveryType('home')"
                                class="px-4 py-2 text-sm font-medium transition-colors">
                                <x-edz.icon name="home" class="w-4 h-4 inline mr-1" />
                                {{ __('merchant_panel.home_delivery_label') }}
                            </button>
                            <button type="button"
                                :class="delivery === 'stopdesk' ? 'bg-brand-500 text-white' : 'bg-surface text-ink'"
                                @click="delivery = 'stopdesk'; $wire.changeDeliveryType('stopdesk')"
                                class="px-4 py-2 text-sm font-medium transition-colors">
                                <x-edz.icon name="building-storefront" class="w-4 h-4 inline mr-1" />
                                {{ __('merchant_panel.stop_desk_label') }}
                            </button>
                        </div>

                        {{-- Wilaya → city --}}
                        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="edz-label">{{ __('merchant_panel.state') }}</label>
                                <x-edz.select wire:model="form.state_id" wire:change="loadCities($event.target.value)"
                                    :options="$this->formAvailableStates !== [] ? $this->formAvailableStates : $this->allStates" option-value="id" option-label="name" option-code="state_code" placeholder="—”"
                                    size="sm" search />
                                @if ($this->formCoverageHint)
                                    <p class="text-xs text-warning-500 mt-1">{{ __("order_flow.{$this->formCoverageHint}") }}</p>
                                @endif
                                @error('form.state_id')
                                    <span class="text-danger-500 text-xs mt-1">{{ $message }}</span>
                                @enderror
                            </div>
<div>
                                <label class="edz-label">{{ __('merchant_panel.city') }}</label>
                                <x-edz.select wire:model="form.city_id" wire:change="rebuildFormOffices()"
                                    :options="$this->formCities" option-value="id"
                                    option-label="name" placeholder="—" size="sm" search
                                    lazy source="loadFormCitiesLazy"
                                    :scope="($this->form['delivery_type'] ?? 'home') . '|' . ($this->form['shipping_provider_id'] ?? '') . '|' . ($this->form['state_id'] ?? '')" />
                                @error('form.city_id')
                                    <span class="text-danger-500 text-xs mt-1">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        {{-- Office (office deliveries only, scoped to company + municipality; hidden
                             for the rider leg — a rider carries to the address) --}}
                        <div x-show="delivery === 'stopdesk'" x-cloak class="mt-4">
                        @if (($this->formPartnerType ?? 'provider') === 'provider')
                            <div class="flex items-center gap-2">
                                <div class="flex-1">
                                    <label class="edz-label">{{ __('merchant_panel.office') }}</label>
                                    <x-edz.select wire:model="form.stopdesk_point_id"
                                        :options="$this->formOffices" option-value="value"
                                        option-label="label" option-hint="hint" option-code="code"
                                        placeholder="{{ __('merchant_panel.select_office') }}" size="sm"
                                        search :disabled="$loadingOffices"
                                        lazy source="loadFormOfficesLazy"
                                        :scope="($this->form['shipping_provider_id'] ?? '') . '|' . ($this->form['state_id'] ?? '') . '|' . ($this->form['city_id'] ?? '') . '|' . $this->formOfficesVersion" />
                                </div>
                                <button type="button" wire:click="refreshFormOffices"
                                    wire:loading.attr="disabled"
                                    class="edz-btn edz-btn--ghost edz-btn--sm mt-5 shrink-0 disabled:opacity-50 disabled:pointer-events-none {{ $loadingOffices ? 'opacity-50 pointer-events-none' : '' }}"
                                    aria-label="{{ __('merchant_panel.refresh_offices') }}">
                                    <x-edz.icon name="arrow-path" class="w-4 h-4" />
                                </button>
                            </div>
                            @if (empty($this->form['shipping_provider_id']))
                                <p class="text-xs text-ink-muted mt-1">{{ __('merchant_panel.select_company_first') }}</p>
                            @elseif (empty($this->form['city_id']))
                                <p class="text-xs text-ink-muted mt-1">{{ __('storefront.select_city_for_desks') }}</p>
                            @elseif (! $this->formHasOffices)
                                <p class="text-xs text-warning-500 mt-1">{{ __('merchant_panel.office_none_for_destination') }}</p>
                            @else
                                <p class="text-xs text-ink-muted mt-1">{{ __('merchant_panel.office_hint') }}</p>
                            @endif
                            @error('form.stopdesk_point_id')
                                <span class="text-danger-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                        @endif
                    </div>

                    {{-- Order Info --}}
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="edz-label">{{ __('merchant_panel.shipment') }}</label>
                            <x-edz.select wire:model="form.shipment_type" :options="$this->formShipmentTypeOptions()" size="sm" />
                        </div>
                        <div>
                            <label class="edz-label">{{ __('merchant_panel.payment_method') }}</label>
                            <x-edz.select wire:model="form.payment_method" :options="[['value' => 'cod', 'label' => __('merchant_panel.cod')]]" size="sm" />
                        </div>
                        <div>
                            <label class="edz-label">{{ __('merchant_panel.weight_kg') }}</label>
                            <input type="number" wire:model="form.weight_kg" step="0.01" class="edz-input text-sm">
                            <p class="text-xs text-ink-muted mt-1">{{ __('order_flow.weight_auto_hint') }}</p>
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
                                                SKU: {{ $item['sku'] ?? '—' }}
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
                                            أ—{{ $dup['total_overlap_qty'] }}
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Order Summary — Shared financial grid (single source for create & edit) --}}
                    @if (!empty($form['items']))
                        @include('livewire.merchant.orders.partials.order-financial-summary')
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
                            wire:loading.class="opacity-50 pointer-events-none">
                            <span>{{ $showEditModal ? __('merchant_panel.update') : __('merchant_panel.create') }}</span>
                            <span
                                class="sr-only">{{ $showEditModal ? __('merchant_panel.update') : __('merchant_panel.create') }}</span>
                        </button>
                    </div>
                </div>
            </form>
        </x-edz.modal>
    @endif

</div>
