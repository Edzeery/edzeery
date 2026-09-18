{{-- Shifts Tab --}}
    @if($tab === 'shifts')
        <div class="flex items-center justify-between mb-4">
            <p class="text-sm text-ink-muted">{{ __('merchant_panel.tab_shifts_desc') }}</p>
            <button wire:click="openShiftModal" class="edz-btn edz-btn--primary edz-btn--sm">
                <x-edz.icon name="check-circle" class="w-4 h-4" />
                {{ __('merchant_panel.new_shift') }}
            </button>
        </div>

        @if(!empty($shifts))
            <div class="edz-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="edz-table">
                        <thead>
                            <tr>
                                <th>{{ __('merchant_panel.agent') }}</th>
                                <th>{{ __('merchant_panel.type') }}</th>
                                <th>{{ __('merchant_panel.hours') }}</th>
                                <th>{{ __('merchant_panel.days') }}</th>
                                <th>{{ __('merchant_panel.max_orders_cap') }}</th>
                                <th>{{ __('merchant_panel.status') }}</th>
                                <th class="text-end">{{ __('merchant_panel.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($shifts as $shift)
                                <tr wire:key="shift-{{ $shift['id'] }}">
                                    <td class="font-medium text-ink">
                                        <div class="flex items-center gap-2">
                                            {{ $shift['membership']['user']['name'] ?? '—' }}
                                            @if(empty($shift['membership']['is_active']))
                                                <span class="edz-badge edz-badge--neutral">{{ __('merchant_panel.member_inactive') }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="capitalize">
                                        {{ $SHIFT_TYPES[$shift['shift_type']] ?? $shift['shift_type'] }}
                                    </td>
                                    <td class="font-mono text-xs">
                                        @php $isOvernight = $shift['start_time'] !== $shift['end_time'] && $shift['start_time'] > $shift['end_time']; @endphp
                                        {{ $shift['start_time'] }} — {{ $shift['end_time'] }}
                                        @if($isOvernight)
                                            <span class="edz-badge edz-badge--brand ms-1">{{ __('merchant_panel.shift_overnight') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-xs">
                                        @if(!empty($shift['days_of_week']))
                                            <span class="inline-flex flex-wrap gap-1">
                                                @foreach($shift['days_of_week'] as $day)
                                                    <span class="edz-badge edz-badge--neutral">{{ $DAYS_OF_WEEK[$day] ?? $day }}</span>
                                                @endforeach
                                            </span>
                                        @else
                                            <span class="text-ink-muted">{{ __('merchant_panel.all_days') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-xs">
                                        @if(!empty($shift['max_concurrent_orders']))
                                            <span class="edz-badge edz-badge--brand">{{ $shift['max_concurrent_orders'] }}</span>
                                        @else
                                            <span class="text-ink-muted">∞</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button type="button" wire:click="toggleShiftActive('{{ $shift['id'] }}')"
                                                class="cursor-pointer {{ $shift['is_active'] ? 'edz-badge edz-badge--success' : 'edz-badge edz-badge--neutral' }}">
                                            {{ $shift['is_active'] ? __('merchant_panel.active') : __('merchant_panel.inactive') }}
                                        </button>
                                    </td>
                                    <td class="text-end">
                                        <div class="flex items-center justify-end gap-1">
                                            <button type="button" aria-label="{{ __('merchant_panel.edit_shift') }}"
                                                    wire:click="openShiftModal('{{ $shift['id'] }}')"
                                                    class="edz-btn edz-btn--ghost edz-btn--sm">
                                                <x-edz.icon name="edit" class="w-4 h-4" />
                                            </button>
<button type="button" aria-label="{{ __('merchant_panel.delete_shift') }}"
                                                    class="edz-btn edz-btn--ghost edz-btn--sm text-danger-500"
                                                    x-data
                                                    x-on:click.prevent="(async () => { if (await EdzSwal.confirmDelete()) await $wire.deleteShift('{{ $shift['id'] }}') })()">
                                                    <x-edz.icon name="x-mark" class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="edz-card p-12 text-center">
                <div class="w-16 h-16 rounded-full bg-surface-secondary flex items-center justify-center mx-auto mb-4">
                    <x-edz.icon name="adjustments" class="w-8 h-8 text-ink-muted opacity-40" />
                </div>
                <p class="text-ink-muted mb-4">{{ __('merchant_panel.no_shifts_yet') }}</p>
                <button wire:click="openShiftModal" class="edz-btn edz-btn--primary edz-btn--sm">
                    <x-edz.icon name="check-circle" class="w-4 h-4" />
                    {{ __('merchant_panel.new_shift') }}
                </button>
            </div>
        @endif
    @endif