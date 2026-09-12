@if ($optionModalRow !== null)
    @php
        $inlineOption = $this->productOptions->firstWhere('id', $this->optionModalCreatedId);
    @endphp

    <x-edz.modal
        :isOpen="true"
        :showCloseButton="false"
        :preventClose="true"
        size="lg"
        wire:key="create-option-modal-{{ $optionModalRow }}-{{ $optionModalCreatedId ?? 'new' }}"
    >
        @if ($optionModalCreatedId === null)
            {{-- Create option --}}
            <span class="edz-modal__handle"></span>

            <div class="edz-card__header">
                <div>
                    <h2 class="edz-card__title">{{ __('products.new_option') }}</h2>
                    <p class="text-sm text-ink-400">{{ __('product_options.new_option_desc') }}</p>
                </div>
                <button type="button" wire:click="closeOptionModal"
                        class="edz-btn edz-btn--ghost edz-btn--sm shrink-0"
                        aria-label="{{ __('buttons.close') }}"
                        :title="'{{ __('buttons.close') }}'">
                    <x-edz.icon name="x-mark" class="h-4 w-4" />
                </button>
            </div>

            <div class="edz-card__body">
                <div class="space-y-4">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="edz-field">
                            <label class="edz-field__label" for="inline-option-name">{{ __('product_options.name') }}</label>
                            <input id="inline-option-name" type="text" class="edz-input @error('optionModal.name') edz-input--error @enderror"
                                   wire:model="optionModal.name"
                                   @keydown.enter.prevent="$wire.createOptionInline()"
                                   placeholder="{{ __('product_options.option_name') }}">
                            @error('optionModal.name')
                                <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="edz-field">
                            <label class="edz-field__label" for="inline-option-type">{{ __('product_options.input_type') }}</label>
                            <x-edz.select
                                wire:model="optionModal.type"
                                :options="$this->optionInputTypes"
                                placeholder="{{ __('product_options.select_type') }}"
                                :error="$errors->first('optionModal.type')"
                                icon="list-bullet"
                            />
                        </div>
                    </div>

                    <div class="mt-4 flex items-center justify-end gap-2 border-t border-surface-border pt-4">
                        <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm" wire:click="closeOptionModal">
                            {{ __('buttons.cancel') }}
                        </button>
                        <button type="button" wire:click="createOptionInline" class="edz-btn edz-btn--primary edz-btn--sm">
                            <x-edz.icon name="check" class="h-4 w-4" />
                            {{ __('buttons.save') }}
                        </button>
                    </div>
                </div>
            </div>
        @else
            {{-- Add values --}}
            <span class="edz-modal__handle"></span>

            <div class="edz-card__header">
                <div>
                    <h2 class="edz-card__title">{{ $inlineOption?->name ?? __('products.new_option') }}</h2>
                    <p class="text-sm text-ink-400">{{ __('products.new_option_done') }}</p>
                </div>
                <button type="button" wire:click="closeOptionModal"
                        class="edz-btn edz-btn--ghost edz-btn--sm shrink-0"
                        aria-label="{{ __('buttons.close') }}"
                        :title="'{{ __('buttons.close') }}'">
                    <x-edz.icon name="x-mark" class="h-4 w-4" />
                </button>
            </div>

            <div class="edz-card__body">
                <div class="mb-2 flex items-center justify-between gap-2">
                    <h3 class="text-sm font-semibold text-ink">{{ __('products.values') }}</h3>
                    <span class="text-xs text-ink-muted">
                        {{ __('products.selected_count', ['count' => $this->optionValuesByOption->get($optionModalCreatedId, collect())->count()]) }}
                    </span>
                </div>

                <div class="flex flex-wrap gap-2">
                    @forelse ($this->optionValuesByOption->get($optionModalCreatedId, collect()) as $optionValue)
                        <span class="edz-multi-select__chip">
                            <span class="edz-multi-select__chip-label">{{ $optionValue->value }}</span>
                            @if (canStore(\App\Enums\Store\StorePermissionEnum::PRODUCT_UPDATE->value))
                                <button type="button"
                                        wire:click="removeOptionValueInline('{{ $optionValue->id }}')"
                                        wire:loading.attr="disabled"
                                        :aria-label="'{{ __('buttons.remove') }}'"
                                        title="{{ __('products.remove_option') }}"
                                        class="edz-multi-select__chip-remove">
                                    <x-edz.icon name="x-mark" class="w-3 h-3" />
                                </button>
                            @endif
                        </span>
                    @empty
                        <span class="text-sm text-ink-muted">{{ __('product_options.no_values') }}</span>
                    @endforelse
                </div>

                @if (canStore(\App\Enums\Store\StorePermissionEnum::PRODUCT_UPDATE->value))
                    <div class="mt-5 rounded-lg border border-surface-border p-4">
                        <label class="edz-field__label" for="inline-option-value">{{ __('products.new_value') }}</label>
                        <div class="mt-2 flex items-center gap-2">
                            <input id="inline-option-value" type="text" class="edz-input min-w-0 flex-1"
                                   wire:model="optionNewValue"
                                   @keydown.enter.prevent="$wire.addOptionValueInline()"
                                   placeholder="{{ __('product_options.add_value_placeholder') }}">
                            <button type="button" wire:click="addOptionValueInline" class="edz-btn edz-btn--secondary edz-btn--sm shrink-0">
                                <x-edz.icon name="plus" class="h-4 w-4" />
                                {{ __('buttons.add') }}
                            </button>
                        </div>
                    </div>
                @endif
            </div>

            <div class="edz-card__footer">
                <button type="button" wire:click="closeOptionModal" class="edz-btn edz-btn--primary edz-btn--sm">
                    <x-edz.icon name="check" class="h-4 w-4" />
                    {{ __('buttons.done') }}
                </button>
            </div>
        @endif
    </x-edz.modal>
@endif