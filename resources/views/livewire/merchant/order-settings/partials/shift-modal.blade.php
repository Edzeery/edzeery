{{-- Shift Modal --}}
    @if($showShiftModal)
    <x-edz.modal :isOpen="true" wire:key="shift-modal-{{ $showShiftModal ? 'open' : 'closed' }}">
        <form wire:submit="saveShift">
            <div class="p-6 space-y-4">
                <h3 class="text-lg font-semibold text-ink">
                    {{ $editingShiftId ? __('merchant_panel.edit_shift') : __('merchant_panel.new_shift') }}
                </h3>

                <div class="space-y-4">
                    <div class="edz-field">
                        <label class="edz-field__label" for="shift-agent">{{ __('merchant_panel.agent') }} *</label>
                        <x-edz.select
                            wire:model="shiftForm.membership_id"
                            :options="$members"
                            option-value="id"
                            option-label="user.name"
                            placeholder="{{ __('merchant_panel.select_agent') }}"
                            :error="$errors->first('shiftForm.membership_id')"
                        />
                    </div>

                    <div class="edz-field">
                        <label class="edz-field__label" for="shift-type">{{ __('merchant_panel.shift_type') }}</label>
                        <x-edz.select
                            wire:model="shiftForm.shift_type"
                            wire:change="onShiftTypeChange"
                            :options="$SHIFT_TYPES"
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="edz-field">
                            <label class="edz-field__label" for="shift-start">{{ __('merchant_panel.start_time') }}</label>
                            <input type="time" id="shift-start" wire:model="shiftForm.start_time"
                                   class="edz-input @error('shiftForm.start_time') edz-input--error @enderror">
                            @error('shiftForm.start_time')
                                <span class="edz-field__error">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="edz-field">
                            <label class="edz-field__label" for="shift-end">{{ __('merchant_panel.end_time') }}</label>
                            <input type="time" id="shift-end" wire:model="shiftForm.end_time"
                                   class="edz-input @error('shiftForm.end_time') edz-input--error @enderror">
                            @error('shiftForm.end_time')
                                <span class="edz-field__error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="edz-field">
                        <span class="edz-field__label">{{ __('merchant_panel.days_of_week') }}</span>
                        <div class="flex flex-wrap gap-2">
                            @foreach($DAYS_OF_WEEK as $dayNum => $dayLabel)
                                <button type="button" wire:click="toggleShiftDay({{ $dayNum }})"
                                        class="cursor-pointer {{ in_array($dayNum, $shiftForm['days_of_week'] ?? []) ? 'edz-badge edz-badge--brand' : 'edz-badge edz-badge--neutral' }}">
                                    {{ $dayLabel }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="edz-field">
                        <label class="edz-field__label" for="shift-max">{{ __('merchant_panel.max_concurrent_orders') }}</label>
                        <input type="number" id="shift-max" wire:model.blur="shiftForm.max_concurrent_orders"
                               min="1" max="9999" inputmode="numeric"
                               placeholder="{{ __('merchant_panel.max_concurrent_orders_placeholder') }}"
                               class="edz-input @error('shiftForm.max_concurrent_orders') edz-input--error @enderror">
                        <p class="edz-field__hint">{{ __('merchant_panel.max_concurrent_orders_hint') }}</p>
                        @error('shiftForm.max_concurrent_orders')
                            <span class="edz-field__error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="flex items-center gap-2 pt-2 border-t border-surface-border">
                        <input type="checkbox" wire:model="shiftForm.is_active" id="shift_active"
                               class="h-4 w-4 rounded border-surface-border text-brand-600">
                        <label for="shift_active" class="text-sm text-ink">{{ __('merchant_panel.active') }}</label>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-surface-border">
                        <button type="button" @click="$wire.set('showShiftModal', false)" class="edz-btn edz-btn--ghost">
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