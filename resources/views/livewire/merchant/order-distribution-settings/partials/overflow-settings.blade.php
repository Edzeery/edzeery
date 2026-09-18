{{-- Overflow settings card — order distribution capacity (confirmation + tracking alike). --}}
<div class="edz-card edz-card--padded">
    <h3 class="text-base font-semibold text-ink mb-1 flex items-center gap-2">
        <x-edz.icon name="trending-up" class="w-5 h-5 text-accent-500" />
        {{ __('merchant_panel.distribution_overflow_group') }}
    </h3>
    <p class="text-xs text-ink-muted mb-5">{{ __('merchant_panel.distribution_overflow_group_desc') }}</p>

    <div class="space-y-6">
        <label
            class="flex items-start gap-3 p-4 rounded-xl border transition-all cursor-pointer
            {{ $overflowEnabled ? 'border-accent-500 bg-accent-surface-subtle' : 'border-surface-border' }}">
            <x-edz.checkbox wire:model="overflowEnabled" class="mt-0.5" />
            <span>
                <span class="block text-sm font-medium text-ink">{{ __('merchant_panel.distribution_overflow_enabled') }}</span>
                <span class="block text-xs text-ink-muted mt-0.5">{{ __('merchant_panel.distribution_overflow_enabled_desc') }}</span>
            </span>
        </label>

        <div>
            <div class="max-w-xs">
                <label for="distribution_overflow_percentage" class="edz-label">{{ __('merchant_panel.distribution_overflow_percentage') }}</label>
                <div class="relative">
                    <input id="distribution_overflow_percentage" type="number" min="0" max="100"
                        wire:model="overflowPercentage" @disabled(! $overflowEnabled)
                        placeholder="10"
                        class="edz-input w-full pe-8 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none {{ $overflowEnabled ? '' : 'opacity-60' }}">
                    <span class="absolute inset-y-0 end-0 flex items-center pe-3 text-sm text-ink-muted">٪</span>
                </div>
            </div>
            <p class="text-xs text-ink-muted mt-1">{{ __('merchant_panel.distribution_overflow_percentage_desc') }}</p>
            @error('overflowPercentage') <p class="text-danger-fg text-xs mt-1">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="mt-6 pt-6 border-t border-surface-border flex justify-end">
        <button type="button" wire:click="save" class="edz-btn edz-btn--primary">
            <x-edz.icon name="save" class="w-4 h-4 me-1" />
            {{ __('buttons.save') }}
        </button>
    </div>
</div>