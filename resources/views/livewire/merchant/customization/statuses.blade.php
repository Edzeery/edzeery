<?php

use App\Domains\Shipping\Support\CarrierStatusDictionary;
use App\Domains\Status\StatusResolver;
use App\Enums\Store\StorePermissionEnum;
use App\Services\Stores\StoreStatusService;

use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;

layout('components.layouts.store');

state([
    'tab' => 'confirmation',
    'confirmationView' => 'customize',
    'storeId' => null,
    'statusList' => [],
    'labels' => [],
    'colors' => [],
    'carrier' => 'noest',
]);

mount(function (): void {
    abort_unless(canStore(StorePermissionEnum::STORE_UPDATE->value), 403);

    $this->storeId = currentStoreId();
    $this->loadConfirmation();
});

$loadConfirmation = function (): void {
    $service = app(StoreStatusService::class);

    $this->statusList = $service->confirmationList((string) $this->storeId);
    $this->labels = collect($this->statusList)->pluck('override_label', 'key')->all();
    $this->colors = collect($this->statusList)->pluck('color', 'key')->all();
};

$resolve = function (string $key) {
    return StatusResolver::resolve('order', $key, (string) $this->storeId);
};

$colorOptions = function (): array {
    return StoreStatusService::COLOR_OPTIONS;
};

$setTab = function (string $tab): void {
    abort_unless(canStore(StorePermissionEnum::STORE_UPDATE->value), 403);

    $this->tab = in_array($tab, ['confirmation', 'carrier_tracking', 'rider_tracking'], true) ? $tab : 'confirmation';
};

$carrierRows = function (): array {
    $rows = [];

    foreach (CarrierStatusDictionary::list($this->carrier) as $row) {
        $rows[] = [
            'raw' => $row['raw'],
            'status' => $row['status'],
            'resolved' => StatusResolver::resolve('tracking', $row['status']->value, (string) $this->storeId),
        ];
    }

    return $rows;
};

$setConfirmationView = function (string $view): void {
    abort_unless(canStore(StorePermissionEnum::STORE_UPDATE->value), 403);

    $this->confirmationView = $view === 'order' ? 'order' : 'customize';
};

$saveChanges = function (): void {
    abort_unless(canStore(StorePermissionEnum::STORE_UPDATE->value), 403);

    $service = app(StoreStatusService::class);

    foreach ($this->statusList as $row) {
        $key = $row['key'];

        $newLabel = trim((string) ($this->labels[$key] ?? ''));
        if ($newLabel !== $row['override_label']) {
            $service->saveLabel((string) $this->storeId, $key, $newLabel);
        }

        $newColor = (string) ($this->colors[$key] ?? $row['color']);
        if ($newColor !== $row['color']) {
            $service->saveColor((string) $this->storeId, $key, $newColor);
        }
    }

    $this->loadConfirmation();

    $this->dispatch('swal', type: 'success', title: __('merchant_panel.settings_saved'));
};

$moveStatus = function (string $key, int $direction): void {
    abort_unless(canStore(StorePermissionEnum::STORE_UPDATE->value), 403);

    app(StoreStatusService::class)->move((string) $this->storeId, $key, $direction);

    $this->loadConfirmation();

    $this->dispatch('swal', type: 'success', title: __('merchant_panel.settings_saved'));
};
?>

<div>
    <x-edz.page-header :title="__('merchant_panel.customization')"
        :description="__('merchant_panel.customization_desc')" />

    <div class="space-y-6">
        {{-- Tabs --}}
        <div class="flex gap-1 border-b border-surface-border overflow-x-auto">
            <button type="button" wire:click="setTab('confirmation')"
                class="inline-flex items-center gap-1.5 px-4 py-2.5 text-sm font-medium border-b-2 transition-colors -mb-px whitespace-nowrap {{ $tab === 'confirmation' ? 'border-brand-500 text-brand-fg' : 'border-transparent text-ink-muted hover:text-ink' }}">
                <x-edz.icon name="check-circle" class="w-4 h-4" />
                {{ __('merchant_panel.tab_confirmation') }}
            </button>
            <button type="button" wire:click="setTab('carrier_tracking')"
                class="inline-flex items-center gap-1.5 px-4 py-2.5 text-sm font-medium border-b-2 transition-colors -mb-px whitespace-nowrap {{ $tab === 'carrier_tracking' ? 'border-brand-500 text-brand-fg' : 'border-transparent text-ink-muted hover:text-ink' }}">
                <x-edz.icon name="truck" class="w-4 h-4" />
                {{ __('merchant_panel.tab_carrier_tracking') }}
            </button>
            <button type="button" wire:click="setTab('rider_tracking')"
                class="inline-flex items-center gap-1.5 px-4 py-2.5 text-sm font-medium border-b-2 transition-colors -mb-px whitespace-nowrap {{ $tab === 'rider_tracking' ? 'border-brand-500 text-brand-fg' : 'border-transparent text-ink-muted hover:text-ink' }}">
                <x-edz.icon name="user" class="w-4 h-4" />
                {{ __('merchant_panel.tab_rider_tracking') }}
            </button>
        </div>

        {{-- Confirmation tab --}}
        @if ($tab === 'confirmation')
            <div class="space-y-4">
                <div class="flex items-center justify-between gap-3 flex-wrap">
                    <div class="flex gap-1">
                        <button type="button" wire:click="setConfirmationView('customize')"
                            class="edz-btn edz-btn--sm {{ $confirmationView === 'customize' ? 'edz-btn--primary' : 'edz-btn--ghost' }}">
                            <x-edz.icon name="adjustments" class="w-4 h-4" />
                            {{ __('merchant_panel.confirmation_customize') }}
                        </button>
                        <button type="button" wire:click="setConfirmationView('order')"
                            class="edz-btn edz-btn--sm {{ $confirmationView === 'order' ? 'edz-btn--primary' : 'edz-btn--ghost' }}">
                            <x-edz.icon name="arrow-up" class="w-4 h-4 rotate-90" />
                            {{ __('merchant_panel.confirmation_reorder') }}
                        </button>
                    </div>

                    @if ($confirmationView === 'customize')
                        <button type="button" wire:click="saveChanges" wire:loading.attr="disabled"
                            class="edz-btn edz-btn--primary edz-btn--sm">
                            <x-edz.icon name="check-circle" class="w-4 h-4" />
                            {{ __('merchant_panel.save') }}
                        </button>
                    @endif
                </div>

                @if ($confirmationView === 'customize')
                    <div class="edz-card edz-card--padded">
                        <div class="overflow-x-auto">
                            <table class="edz-table">
                                <thead>
                                    <tr>
                                        <th class="w-14">{{ __('merchant_panel.status_position') }}</th>
                                        <th>{{ __('merchant_panel.status') }}</th>
                                        <th>{{ __('merchant_panel.status_label') }}</th>
                                        <th class="w-48">{{ __('merchant_panel.status_color') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($statusList as $index => $row)
                                        @php $resolved = $this->resolve($row['key']); @endphp
                                        <tr wire:key="status-row-{{ $row['key'] }}">
                                            <td class="text-ink-muted font-mono text-xs">{{ $index + 1 }}</td>
                                            <td>
                                                <div class="flex items-center gap-2">
                                                    <span
                                                        class="inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-0.5 rounded-full {{ $resolved->classes() }}">
                                                        {{ $resolved->label }}
                                                    </span>
                                                    @if ($row['has_override'])
                                                        <span class="edz-badge edz-badge--neutral">{{ __('merchant_panel.status_custom') }}</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <input type="text" maxlength="255"
                                                    wire:model.defer="labels.{{ $row['key'] }}"
                                                    placeholder="{{ $resolved->label }}"
                                                    class="edz-input edz-input--sm w-full" />
                                            </td>
                                            <td>
                                                <select wire:model.defer="colors.{{ $row['key'] }}"
                                                    class="edz-input edz-input--sm w-full">
                                                    @foreach ($this->colorOptions() as $variant)
                                                        <option value="{{ $variant }}">{{ __('merchant_panel.color_'.$variant) }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="edz-card edz-card--padded">
                        <ul class="divide-y divide-surface-border">
                            @foreach ($statusList as $index => $row)
                                @php $resolved = $this->resolve($row['key']); @endphp
                                <li wire:key="status-order-{{ $row['key'] }}"
                                    class="flex items-center gap-3 py-3">
                                    <span class="edz-badge edz-badge--neutral font-mono">{{ $index + 1 }}</span>
                                    <span
                                        class="inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-0.5 rounded-full {{ $resolved->classes() }}">
                                        {{ $resolved->label }}
                                    </span>
                                    <div class="ms-auto flex items-center gap-1">
                                        <button type="button" wire:click="moveStatus('{{ $row['key'] }}', -1)"
                                            @disabled($index === 0) title="{{ __('merchant_panel.move_up') }}"
                                            class="edz-btn edz-btn--ghost edz-btn--icon disabled:opacity-30">
                                            <x-edz.icon name="arrow-up" class="w-4 h-4" />
                                        </button>
                                        <button type="button" wire:click="moveStatus('{{ $row['key'] }}', 1)"
                                            @disabled($index === count($statusList) - 1) title="{{ __('merchant_panel.move_down') }}"
                                            class="edz-btn edz-btn--ghost edz-btn--icon disabled:opacity-30">
                                            <x-edz.icon name="arrow-down" class="w-4 h-4" />
                                        </button>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        @endif

        {{-- Carrier tracking tab --}}
        @if ($tab === 'carrier_tracking')
            <div class="space-y-4">
                <div class="flex items-end justify-between gap-3 flex-wrap">
                    <div class="w-72 max-w-full">
                        <label for="carrier-select" class="block text-sm font-medium text-ink mb-1">
                            {{ __('merchant_panel.carrier_tracking_select') }}
                        </label>
                        <x-edz.select id="carrier-select" wire:model="carrier"
                            :options="\App\Domains\Shipping\Support\CarrierStatusDictionary::carrierOptions()"
                            option-value="value" option-label="label" placeholder="—" />
                    </div>
                    <p class="text-sm text-ink-muted max-w-sm">{{ __('merchant_panel.carrier_tracking_hint') }}</p>
                </div>

                <div class="edz-card edz-card--padded">
                    <div class="overflow-x-auto">
                        <table class="edz-table">
                            <thead>
                                <tr>
                                    <th>{{ __('merchant_panel.carrier_tracking_raw') }}</th>
                                    <th>{{ __('merchant_panel.carrier_tracking_applied') }}</th>
                                    <th>{{ __('merchant_panel.carrier_tracking_label') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($this->carrierRows() as $row)
                                    <tr wire:key="carrier-row-{{ $carrier }}-{{ $row['raw'] }}">
                                        <td class="font-mono text-xs">{{ $row['raw'] }}</td>
                                        <td>
                                            <span
                                                class="inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-0.5 rounded-full {{ $row['resolved']->classes() }}">
                                                {{ $row['status']->value }}
                                            </span>
                                        </td>
                                        <td class="text-sm">{{ $row['resolved']->label }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        {{-- Rider tracking tab --}}
        @if ($tab === 'rider_tracking')
            <div class="edz-card edz-card--padded">
                <div class="flex flex-col items-center justify-center gap-3 py-16 text-center">
                    <div class="w-12 h-12 rounded-xl bg-surface-secondary flex items-center justify-center">
                        <x-edz.icon name="user" class="w-6 h-6 text-ink-muted" />
                    </div>
                    <p class="text-sm text-ink-muted max-w-sm">{{ __('merchant_panel.customization_empty') }}</p>
                </div>
            </div>
        @endif
    </div>
</div>