{{-- Rider tab (Phase D) — rider overview + per-rider shipments. Data: riderRiders / selectedRiderId / riderShipments. --}}
<div>
    @if (empty($this->riderRiders))
        {{-- Empty state — no store riders configured yet --}}
        <div class="edz-card edz-card--padded text-center py-12">
            <x-edz.icon name="users" class="w-10 h-10 mx-auto mb-3 text-ink-muted" />
            <h4 class="text-base font-bold text-ink">{{ __('order_flow.rider_tab_empty_title') }}</h4>
            <p class="mt-1 text-sm text-ink-muted max-w-md mx-auto">{{ __('order_flow.rider_tab_empty_hint') }}</p>
            @if (canStore(\App\Enums\Store\StorePermissionEnum::DELIVERY_RIDERS_VIEW->value))
                <a href="{{ route('merchant.delivery.riders', currentStore()) }}"
                    class="edz-btn edz-btn--ghost edz-btn--sm mt-4">
                    <x-edz.icon name="arrow-right" class="w-4 h-4" />
                    {{ __('order_flow.rider_manage_link') }}
                </a>
            @endif
        </div>
    @else
        {{-- Rider overview — one card per rider; tap to expand its shipments --}}
        <div class="space-y-3">
            @foreach ($this->riderRiders as $rRider)
                <div class="edz-card p-4">
                    <button type="button" wire:click="toggleRider('{{ $rRider['id'] }}')"
                        class="w-full flex items-center gap-3 text-start">
                        <div
                            class="flex items-center justify-center w-10 h-10 rounded-full bg-accent-surface text-accent-fg-strong shrink-0 font-bold">
                            {{ mb_substr($rRider['name'], 0, 1) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-ink truncate">{{ $rRider['name'] }}</span>
                                @if (! $rRider['is_active'])
                                    <span
                                        class="text-xs px-2 py-0.5 rounded-full bg-surface-border text-ink-muted">{{ __('merchant_panel.rider_inactive') }}</span>
                                @endif
                            </div>
                            <div class="text-xs text-ink-muted truncate">
                                {{ $rRider['vehicle_label'] }}
                                <span class="ms-1" dir="ltr">{{ $rRider['phone'] }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 shrink-0">
                            <div @class(['text-center', 'hidden' => $rRider['total'] === 0])>
                                <div class="text-lg font-bold tabular-nums leading-none {{ $rRider['active'] > 0 ? 'text-ink' : 'text-ink-muted' }}">
                                    {{ $rRider['active'] }}</div>
                                <div class="text-[11px] text-ink-muted mt-0.5">{{ __('order_flow.rider_active') }}</div>
                            </div>
                            <div class="text-center">
                                <div class="text-lg font-bold text-ink tabular-nums leading-none">{{ $rRider['total'] }}</div>
                                <div class="text-[11px] text-ink-muted mt-0.5">{{ __('order_flow.rider_shipments_count') }}</div>
                            </div>
                            <x-edz.icon name="chevron-down"
                                class="w-4 h-4 text-ink-muted shrink-0 transition-transform {{ $this->selectedRiderId === $rRider['id'] ? 'rotate-180' : '' }}" />
                        </div>
                    </button>

                    <div class="mt-3 h-1 w-full rounded-full bg-surface-border overflow-hidden">
                        <div class="h-1 rounded-full bg-accent-600 transition-all"
                            style="width: {{ $rRider['total'] ? min(100, (int) round(($rRider['active'] / $rRider['total']) * 100)) : 0 }}%"></div>
                    </div>
                </div>

                @if ($this->selectedRiderId === $rRider['id'])
                    <div class="ps-2 sm:ps-12">
                        @include('livewire.merchant.tracking.partials.tracking-rider-shipments')
                    </div>
                @endif
            @endforeach
        </div>
    @endif
</div>