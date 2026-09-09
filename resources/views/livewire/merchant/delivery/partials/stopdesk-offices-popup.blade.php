@props(['popupStateName' => '', 'popupStateId' => null, 'popupOffices' => [], 'selectedProviderId' => null])

@php
    $defaultStateId = ($popupStateId ?? '') === '__unassigned__' ? '' : (string) ($popupStateId ?? '');
@endphp

<x-edz.modal :isOpen="true" :showCloseButton="false" :preventClose="true" size="lg"
    wire:key="offices-popup-{{ $selectedProviderId }}-{{ $popupStateId }}">
    <div class="p-6">
        {{-- Header: state name + close --}}
        <div class="flex items-center justify-between mb-1">
            <h3 class="text-lg font-bold text-ink">{{ $popupStateName }}</h3>
            <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm"
                    wire:click="closeOfficesPopup">
                <x-edz.icon name="x-mark" class="w-5 h-5" />
            </button>
        </div>
        <p class="text-sm text-ink-muted mb-5">{{ __('merchant_panel.stopdesk_no_offices_desc') }}</p>

        @if (empty($popupOffices))
            <div class="py-10 text-center">
                <div class="w-14 h-14 rounded-full bg-surface-secondary flex items-center justify-center mx-auto mb-4">
                    <x-edz.icon name="map-pin" class="w-7 h-7 text-ink-muted opacity-40" />
                </div>
                <p class="text-ink-muted mb-4">{{ __('merchant_panel.stopdesk_no_offices') }}</p>
                @if (canStore(\App\Enums\Store\StorePermissionEnum::DELIVERY_PRICING_MANAGE->value))
                    <button type="button" wire:click="openStopdeskModal(null, '{{ $defaultStateId }}')"
                        class="edz-btn edz-btn--primary edz-btn--sm">
                        <x-edz.icon name="plus" class="w-4 h-4" />
                        {{ __('merchant_panel.new_stopdesk') }}
                    </button>
                @endif
            </div>
        @else
            @php
                $officeGroups = collect($popupOffices)
                    ->groupBy(fn ($point) => ! empty($point['city']['id']) ? (string) $point['city']['id'] : '__state_wide__')
                    ->map(function ($group, $key) {
                        $first = $group->first();

                        return [
                            'key' => $key,
                            'name' => $key === '__state_wide__'
                                ? __('merchant_panel.stopdesk_state_wide')
                                : ($first['city']['name'] ?? ''),
                            'offices' => $group->values()->all(),
                        ];
                    })
                    ->sortBy(fn ($g) => [$g['key'] === '__state_wide__' ? 1 : 0, $g['name']])
                    ->values()
                    ->all();
            @endphp

            <div class="space-y-5 max-h-[26rem] overflow-y-auto edz-scroll pr-1">
                @foreach ($officeGroups as $group)
                    <div>
                        <h4 class="flex items-center gap-2 text-sm font-semibold text-ink mb-2">
                            <x-edz.icon name="map-pin" class="w-4 h-4 text-ink-muted" />
                            {{ $group['name'] }}
                            <span class="text-xs text-ink-muted font-normal">({{ count($group['offices']) }})</span>
                            @if ($group['key'] === '__state_wide__')
                                <span class="edz-badge edz-badge--info">{{ __('merchant_panel.stopdesk_state_wide_hint') }}</span>
                            @endif
                        </h4>
                        <div class="space-y-2">
                        @foreach ($group['offices'] as $point)
                            <div wire:key="office-{{ $point['id'] }}" class="edz-card edz-card--padded">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <p class="font-semibold text-ink">{{ $point['name'] }}</p>
                                            @if ($point['synced'])
                                                <span class="edz-badge edz-badge--info">
                                                    <x-edz.icon name="check-circle" class="w-3.5 h-3.5" />
                                                    {{ __('merchant_panel.stopdesk_synced') }}
                                                </span>
                                            @endif
                                            <span class="{{ $point['is_active'] ? 'edz-badge edz-badge--success' : 'edz-badge edz-badge--neutral' }}">
                                                {{ $point['is_active'] ? __('merchant_panel.stopdesk_active') : __('merchant_panel.stopdesk_inactive') }}
                                            </span>
                                        </div>

                                        @if (! empty($point['city']) || ! empty($point['state']))
                                            <p class="flex items-center gap-1.5 mt-1.5 text-sm text-ink-muted">
                                                <x-edz.icon name="map-pin" class="w-3.5 h-3.5" />
                                                {{ $point['state']['name'] ?? '' }}{{ ! empty($point['city']) ? ($point['state'] ? ' — ' : '') . $point['city']['name'] : '' }}
                                            </p>
                                        @endif
                                        @if (! empty($point['address']))
                                            <p class="flex items-center gap-1.5 mt-1 text-sm text-ink-muted">
                                                <x-edz.icon name="home" class="w-3.5 h-3.5" />
                                                {{ $point['address'] }}
                                            </p>
                                        @endif
                                        @if (! empty($point['phone']))
                                            <p class="flex items-center gap-1.5 mt-1 text-sm text-ink-muted" dir="ltr">
                                                <x-edz.icon name="phone" class="w-3.5 h-3.5" />
                                                <span class="text-start">{{ $point['phone'] }}</span>
                                            </p>
                                        @endif
                                    </div>

                                    @if (canStore(\App\Enums\Store\StorePermissionEnum::DELIVERY_PRICING_MANAGE->value))
                                        <div class="flex items-center gap-1 shrink-0">
                                            <button type="button" aria-label="{{ __('merchant_panel.edit_stopdesk') }}"
                                                    wire:click="openStopdeskModal('{{ $point['id'] }}')"
                                                    class="edz-btn edz-btn--ghost edz-btn--sm">
                                                <x-edz.icon name="edit" class="w-4 h-4" />
                                            </button>
                                            <button type="button" aria-label="{{ __('merchant_panel.confirm_delete_stopdesk') }}"
                                                    class="edz-btn edz-btn--ghost edz-btn--sm text-danger-500"
                                                    x-data
                                                    x-on:click.prevent="(async () => { if (await EdzSwal.confirmDelete()) await $wire.deleteStopdesk('{{ $point['id'] }}') })()">
                                                <x-edz.icon name="trash" class="w-4 h-4" />
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            @if (canStore(\App\Enums\Store\StorePermissionEnum::DELIVERY_PRICING_MANAGE->value))
                <div class="mt-4 pt-4 border-t border-surface-border flex justify-end">
                    <button type="button" wire:click="openStopdeskModal(null, '{{ $defaultStateId }}')"
                        class="edz-btn edz-btn--primary edz-btn--sm">
                        <x-edz.icon name="plus" class="w-4 h-4" />
                        {{ __('merchant_panel.new_stopdesk') }}
                    </button>
                </div>
            @endif
        @endif
    </div>
</x-edz.modal>