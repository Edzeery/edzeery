{{-- Assign Products Modal --}}
    <div class="contents" @edz-modal-closed.window="$wire.set('showAssignModal', false)">
    @if($showAssignModal)
    <div @edz-modal-closed.window="$wire.set('showAssignModal', false)">
        <x-edz.modal :isOpen="true" wire:key="assign-modal-{{ $showAssignModal ? 'open' : 'closed' }}">
        <form wire:submit="saveAssignments">
            <div class="p-6 space-y-4">
                <h3 class="text-lg font-semibold text-ink">{{ __('merchant_panel.assign_products') }}</h3>

                <div class="space-y-4">
                    <div class="edz-field">
                        <label class="edz-field__label" for="assign-agent">{{ __('merchant_panel.agent') }} *</label>
                        <x-edz.select
                            wire:model="assignForm.membership_id"
                            :options="$members"
                            option-value="id"
                            option-label="user.name"
                            placeholder="{{ __('merchant_panel.select_agent') }}"
                            :error="$errors->first('assignForm.membership_id')"
                        />
                    </div>

                    <div class="edz-field">
                        <label class="edz-field__label" for="assign-search">{{ __('merchant_panel.search_products') }}</label>
                        <x-edz.product-multi-picker
                            :options="$this->searchAssignProducts"
                            :selected="$assignForm['product_ids'] ?? []"
                            :selected-names="$assignProductNames"
                            toggle="toggleAssignProduct"
                            model="productSearch"
                            :placeholder="__('merchant_panel.search_products_to_add')"
                            :empty-message="__('merchant_panel.list_no_products_found')" />
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-surface-border">
                        <button type="button" @click="$wire.set('showAssignModal', false)" class="edz-btn edz-btn--ghost">
                            {{ __('buttons.cancel') }}
                        </button>
                        <button type="submit" class="edz-btn edz-btn--primary">
                            <x-edz.icon name="check-circle" class="w-4 h-4" />
                            {{ __('merchant_panel.save') }}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </x-edz.modal>
    @endif
    </div>