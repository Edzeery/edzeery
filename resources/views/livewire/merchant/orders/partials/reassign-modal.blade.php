{{-- Shared Reassign Modal (P34.4): used by orders (confirm) and tracking pages.
     Params (passed via @include):
       $reassignOpen        (bool)   whether the modal is open
       $reassignSubmit      (string) Livewire method name to invoke on submit
       $reassignCloseSet    (string) Livewire set() key to close (e.g. 'showReassignModal')
       $reassignModel       (string) Livewire property bound to the radio selection
       $reassignTargetId    (string) currently selected membership id
       $reassignCandidates  (array)  candidate rows from AssignmentCandidateResolver
       $reassignTitle       (string) modal heading --}}
@if ($reassignOpen)
    <x-edz.modal :isOpen="true" :showCloseButton="false" wire:key="shared-reassign-modal">
        <div class="p-6 space-y-4">
            <h3 class="text-lg font-bold text-ink">{{ $reassignTitle }}</h3>

            @php
                $reassignSelected = collect($reassignCandidates)->firstWhere('id', $reassignTargetId);
                $reassignOverCap = $reassignSelected
                    ? ($reassignSelected['cap'] !== null && (int) $reassignSelected['open'] >= (int) $reassignSelected['cap'])
                    : false;
            @endphp

            <fieldset>
                <label class="edz-label mb-2">{{ __('merchant_panel.assign_to') }} *</label>

                @if (empty($reassignCandidates))
                    <p class="text-sm text-ink-muted">{{ __('merchant_panel.reassign_no_candidates') }}</p>
                @else
                    <div class="space-y-2 max-h-72 overflow-y-auto pe-1">
                        @foreach ($reassignCandidates as $candidate)
                            <label wire:key="candidate-{{ $candidate['id'] }}"
                                class="flex items-center gap-3 rounded-xl border px-3 py-2.5 cursor-pointer transition-colors {{ $candidate['id'] === $reassignTargetId ? 'border-brand-500 bg-brand-500/5' : 'border-surface-border hover:border-brand-300 bg-white' }}">
                                <input type="radio" class="sr-only" wire:model.live="{{ $reassignModel }}"
                                    value="{{ $candidate['id'] }}">
                                <span class="flex items-center justify-center w-8 h-8 rounded-full bg-surface-tertiary text-ink-muted text-sm font-semibold shrink-0">
                                    {{ mb_substr($candidate['name'], 0, 1) }}
                                </span>
                                <span class="flex-1 min-w-0">
                                    <span class="flex items-center gap-2 flex-wrap">
                                        <span class="text-sm font-medium text-ink truncate">{{ $candidate['name'] }}</span>
                                        @if ($candidate['on_shift'])
                                            <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-success-700 bg-success-500/10 rounded-full px-1.5 py-0.5">
                                                <span class="w-1.5 h-1.5 rounded-full bg-success-500"></span>
                                                {{ __('merchant_panel.on_shift') }}
                                            </span>
                                        @endif
                                        @if ($candidate['dual_role'])
                                            <span class="inline-flex items-center text-[10px] font-semibold text-accent-fg bg-accent-surface rounded-full px-1.5 py-0.5">
                                                {{ __('merchant_panel.dual_role_badge') }}
                                            </span>
                                        @endif
                                    </span>
                                    <span class="block text-xs text-ink-muted mt-0.5">
                                        {{ __('merchant_panel.current_load') }}:
                                        <span class="font-medium tabular-nums text-ink">
                                            {{ $candidate['cap'] !== null
                                                ? $candidate['open'] . ' / ' . $candidate['cap']
                                                : __('merchant_panel.unlimited') }}
                                        </span>
                                    </span>
                                </span>
                                <span class="shrink-0 ms-2">
                                    @if ($candidate['id'] === $reassignTargetId)
                                        <x-edz.icon name="check" class="w-4 h-4 text-brand-500" />
                                    @endif
                                </span>
                            </label>
                        @endforeach
                    </div>
                @endif
            </fieldset>

            @if ($reassignOverCap)
                <div class="flex items-start gap-2 rounded-xl border border-warning-300/60 bg-warning-500/5 px-3 py-2.5">
                    <x-edz.icon name="exclamation-triangle" class="w-4 h-4 shrink-0 mt-0.5 text-warning-600" />
                    <p class="text-sm text-warning-800">{{ __('merchant_panel.reassign_over_capacity_warning') }}</p>
                </div>
            @endif

            <div class="flex justify-end gap-2">
                <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm"
                    wire:click="set('{{ $reassignCloseSet }}', false)">{{ __('merchant_panel.cancel') }}</button>
                <button wire:click="{{ $reassignSubmit }}" class="edz-btn edz-btn--primary edz-btn--sm"
                    wire:loading.attr="disabled" wire:loading.class="opacity-50 pointer-events-none">
                    <span>{{ __('merchant_panel.reassign') }}</span>
                </button>
            </div>
        </div>
    </x-edz.modal>
@endif