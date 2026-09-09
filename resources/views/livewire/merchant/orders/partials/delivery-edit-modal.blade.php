<div x-data="{ delivery: $wire.form.delivery_type }"
    x-init="$watch('delivery', v => $wire.set('form.delivery_type', v))"
    x-effect="delivery = $wire.form.delivery_type">
    @if ($showDeliveryModal)
        <x-edz.modal :isOpen="true" :showCloseButton="false" :preventClose="false" size="md"
            wire:key="delivery-edit-{{ $deliveryOrderId }}">
            <form wire:submit="saveDeliveryModal">
                <div class="p-6 space-y-5">
                    {{-- Header --}}
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-ink">{{ __('merchant_panel.edit_delivery') }}</h3>
                        <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm"
                            wire:click="closeDeliveryModal">
                            <x-edz.icon name="x-mark" class="w-5 h-5" />
                        </button>
                    </div>

                    {{-- Delivery cascade — company → type → wilaya → city → office --}}
                    <label class="edz-label">{{ __('merchant_panel.shipping_company') }}</label>
                    @if (count($this->allProviders) > 1)
                        <x-edz.select wire:model="form.shipping_provider_id"
                            wire:change="applyProviderScope($event.target.value)"
                            :options="$this->allProviders" option-value="id" option-label="name"
                            placeholder="{{ __('merchant_panel.select_company') }}" size="sm"
                            search :disabled="$loadingOffices" class="edz-company-select" />
                    @elseif (count($this->allProviders) === 1)
                        @php $singleProvider = $this->allProviders[0]; @endphp
                        <div data-edz-company-single
                            class="edz-company-single flex items-center gap-2 px-3 py-2 rounded-lg border border-surface-border bg-surface-secondary">
                            <x-edz.icon name="truck" class="w-4 h-4 text-brand-600 shrink-0" />
                            <span class="text-sm font-medium text-ink truncate">{{ $singleProvider['name'] }}</span>
                            <span class="text-xs text-ink-muted shrink-0">{{ __('merchant_panel.default_provider') }}</span>
                        </div>
                    @else
                        <x-edz.select wire:model="form.shipping_provider_id"
                            wire:change="applyProviderScope($event.target.value)"
                            :options="$this->allProviders" option-value="id" option-label="name"
                            placeholder="{{ __('merchant_panel.select_company') }}" size="sm"
                            search :disabled="$loadingOffices" class="edz-company-select" />
                    @endif
                    @error('form.shipping_provider_id')
                        <span class="text-danger-500 text-xs mt-1">{{ $message }}</span>
                    @enderror

                    <div>
                        <label class="edz-label">{{ __('merchant_panel.delivery') }}</label>
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
                    </div>

                    {{-- Wilaya → city (wilayas scoped to the carrier for office deliveries) --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="edz-label">{{ __('merchant_panel.state') }}</label>
                            <x-edz.select wire:model="form.state_id" wire:change="loadCities($event.target.value)"
                                :options="$this->formAvailableStates !== [] ? $this->formAvailableStates : $this->allStates" option-value="id" option-label="name" option-code="state_code" placeholder="—"
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
                                :options="$this->allCities" option-value="id"
                                option-label="name" placeholder="—" size="sm" search />
                            @error('form.city_id')
                                <span class="text-danger-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Office (office deliveries only, scoped to company + municipality) --}}
                    <div x-show="delivery === 'stopdesk'" x-cloak>
                        <div class="flex items-center gap-2">
                            <div class="flex-1">
                                <label class="edz-label">{{ __('merchant_panel.office') }}</label>
                                <x-edz.select wire:model="form.stopdesk_point_id"
                                    :options="$this->formOffices" option-value="value"
                                    option-label="label" option-hint="hint" option-code="code"
                                    placeholder="{{ __('merchant_panel.select_office') }}" size="sm"
                                    search :disabled="$loadingOffices" />
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
                        @elseif (empty($this->formOffices))
                            <p class="text-xs text-warning-500 mt-1">{{ __('merchant_panel.office_none_for_destination') }}</p>
                        @else
                            <p class="text-xs text-ink-muted mt-1">{{ __('merchant_panel.office_hint') }}</p>
                        @endif
                        @error('form.stopdesk_point_id')
                            <span class="text-danger-500 text-xs mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Submit --}}
                    <div class="flex justify-end gap-2 pt-2 border-t border-surface-border">
                        <button type="button" class="edz-btn edz-btn--ghost" wire:click="closeDeliveryModal">
                            {{ __('buttons.cancel') }}
                        </button>
                        <button type="submit" class="edz-btn edz-btn--primary" wire:loading.attr="disabled"
                            wire:loading.class="opacity-50 pointer-events-none">
                            <span>{{ __('merchant_panel.update') }}</span>
                            <span class="sr-only">{{ __('merchant_panel.update') }}</span>
                        </button>
                    </div>
                </div>
            </form>
        </x-edz.modal>
    @endif
</div>
