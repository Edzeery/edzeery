@props(['stateRows' => [], 'selectedProviderId' => null])

@if (empty($stateRows))
    <div class="edz-card p-12 text-center">
        <div class="w-16 h-16 rounded-full bg-surface-secondary flex items-center justify-center mx-auto mb-4">
            <x-edz.icon name="map-pin" class="w-8 h-8 text-ink-muted opacity-40" />
        </div>
        <p class="text-ink-muted mb-4">{{ __('merchant_panel.no_stopdesk_yet') }}</p>
        @if (canStore(\App\Enums\Store\StorePermissionEnum::DELIVERY_PRICING_MANAGE->value))
            <button wire:click="openStopdeskModal" class="edz-btn edz-btn--primary edz-btn--sm">
                <x-edz.icon name="plus" class="w-4 h-4" />
                {{ __('merchant_panel.new_stopdesk') }}
            </button>
        @endif
    </div>
@else
    <div class="edz-card overflow-hidden">
        {{-- Header row --}}
        <div class="grid grid-cols-12 gap-3 px-5 py-3 bg-surface-secondary text-xs font-semibold uppercase tracking-wide text-ink-muted">
            <div class="col-span-12 sm:col-span-6 lg:col-span-8">{{ __('merchant_panel.state') }}</div>
            <div class="col-span-6 sm:col-span-3 lg:col-span-2">{{ __('merchant_panel.stopdesk_offices') }}</div>
            <div class="col-span-6 sm:col-span-3 lg:col-span-2 text-end">{{ __('merchant_panel.actions') }}</div>
        </div>
        <div class="border-t border-surface-border"></div>

        {{-- Only states with at least one office appear: an API-integrated
             company stays empty until synced once (general sync lives in the
             company panel header); manual companies list what was added in the
             chosen company context. --}}
        <div class="divide-y divide-surface-border">
            @foreach ($stateRows as $row)
                <div wire:key="state-row-{{ $selectedProviderId }}-{{ $row['key'] }}"
                     class="grid grid-cols-12 gap-3 px-5 py-3.5 items-center hover:bg-surface-secondary/60 transition-colors">
                    <div class="col-span-12 sm:col-span-6 lg:col-span-8 flex items-center gap-2 min-w-0">
                        <span class="text-sm font-medium text-ink truncate">
                        @if (!empty($row['code']))
                            <span class="edz-code-badge shrink-0">{{ $row['code'] }}</span>
                        @endif
                        {{ $row['name'] }}
                    </span>
                        @if ($row['key'] === '__unassigned__')
                            <span class="edz-badge edz-badge--warning shrink-0">{{ __('merchant_panel.stopdesk_unassigned') }}</span>
                        @endif
                    </div>
                    <div class="col-span-6 sm:col-span-3 lg:col-span-2 flex items-center gap-1.5">
                        <span class="text-sm font-semibold text-ink">{{ $row['count'] }}</span>
                        @if ($row['synced_count'] > 0)
                            <span class="text-xs text-ink-muted">({{ $row['synced_count'] }} {{ __('merchant_panel.stopdesk_synced') }})</span>
                        @endif
                    </div>
                    <div class="col-span-6 sm:col-span-3 lg:col-span-2 lg:justify-self-end">
                        <button type="button" wire:click="openOfficesPopup('{{ $row['key'] }}')"
                            class="edz-btn edz-btn--ghost edz-btn--sm">
                            <x-edz.icon name="pencil" class="w-4 h-4" />
                            {{ __('merchant_panel.stopdesk_manage_offices') }}
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif