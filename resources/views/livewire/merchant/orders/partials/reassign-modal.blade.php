{{-- Reassign Modal (P24.2): reassign an order to another store member --}}
@if ($showReassignModal)
    <x-edz.modal :isOpen="true" :showCloseButton="false" wire:key="order-reassign-modal">
        <div class="p-6 space-y-4">
            <h3 class="text-lg font-bold text-ink">{{ __('merchant_panel.reassign_order') }}</h3>
            <div>
                <label class="edz-label">{{ __('merchant_panel.assign_to') }} *</label>
                <x-edz.select wire:model="reassignMembershipId" :options="$allMembers" option-value="id"
                    option-label="user.name" placeholder="{{ __('merchant_panel.select_agent') }}"
                    size="sm" />
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm"
                    wire:click="set('showReassignModal', false)">{{ __('merchant_panel.cancel') }}</button>
                <button wire:click="submitReassign" class="edz-btn edz-btn--primary edz-btn--sm"
                    wire:loading.attr="disabled" wire:loading.class="opacity-50 pointer-events-none">
                    <span>{{ __('merchant_panel.reassign') }}</span>
                </button>
            </div>
        </div>
    </x-edz.modal>
@endif