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
    'riderView' => 'customize',
    'storeId' => null,
    'statusList' => [],
    'labels' => [],
    'colors' => [],
    'riderStatusList' => [],
    'riderLabels' => [],
    'riderColors' => [],
    'carrier' => 'noest',
    'showAddConfirmation' => false,
    'showAddRider' => false,
    'newConfirmationLabel' => '',
    'newConfirmationColor' => 'gray',
    'newConfirmationLinkedTo' => 'confirmed',
    'newRiderLabel' => '',
    'newRiderColor' => 'gray',
]);

mount(function (): void {
    abort_unless(canStore(StorePermissionEnum::STORE_UPDATE->value), 403);

    $this->storeId = currentStoreId();
    $this->loadConfirmation();
    $this->loadRider();
});

$loadConfirmation = function (): void {
    $service = app(StoreStatusService::class);

    $this->statusList = $service->confirmationList((string) $this->storeId);
    $this->labels = collect($this->statusList)->pluck('override_label', 'key')->all();
    $this->colors = collect($this->statusList)->pluck('color', 'key')->all();
};

$loadRider = function (): void {
    $service = app(StoreStatusService::class);

    $this->riderStatusList = $service->riderList((string) $this->storeId);
    $this->riderLabels = collect($this->riderStatusList)->pluck('override_label', 'key')->all();
    $this->riderColors = collect($this->riderStatusList)->pluck('color', 'key')->all();
};

$resolve = function (string $key) {
    return StatusResolver::resolve('order', $key, (string) $this->storeId);
};

$resolveTracking = function (string $key) {
    return StatusResolver::resolve('tracking', $key, (string) $this->storeId);
};

$confirmationOptions = function (): array {
    return collect(StoreStatusService::CONFIRMATION_KEYS)
        ->map(fn (string $key) => [
            'value' => $key,
            'label' => StatusResolver::resolve('order', $key, (string) $this->storeId)->label,
        ])
        ->all();
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
            'meaning' => $row['meaning'],
            'resolved' => StatusResolver::resolve('tracking', $row['status']->value, (string) $this->storeId),
        ];
    }

    return $rows;
};

$setConfirmationView = function (string $view): void {
    abort_unless(canStore(StorePermissionEnum::STORE_UPDATE->value), 403);

    $this->confirmationView = $view === 'order' ? 'order' : 'customize';
};

$setRiderView = function (string $view): void {
    abort_unless(canStore(StorePermissionEnum::STORE_UPDATE->value), 403);

    $this->riderView = $view === 'order' ? 'order' : 'customize';
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

$saveRiderChanges = function (): void {
    abort_unless(canStore(StorePermissionEnum::STORE_UPDATE->value), 403);

    $service = app(StoreStatusService::class);

    foreach ($this->riderStatusList as $row) {
        $key = $row['key'];

        $newLabel = trim((string) ($this->riderLabels[$key] ?? ''));
        if ($newLabel !== $row['override_label']) {
            $service->riderSaveLabel((string) $this->storeId, $key, $newLabel);
        }

        $newColor = (string) ($this->riderColors[$key] ?? $row['color']);
        if ($newColor !== $row['color']) {
            $service->riderSaveColor((string) $this->storeId, $key, $newColor);
        }
    }

    $this->loadRider();

    $this->dispatch('swal', type: 'success', title: __('merchant_panel.settings_saved'));
};

$moveStatus = function (string $key, int $direction): void {
    abort_unless(canStore(StorePermissionEnum::STORE_UPDATE->value), 403);

    app(StoreStatusService::class)->move((string) $this->storeId, $key, $direction);

    $this->loadConfirmation();

    $this->dispatch('swal', type: 'success', title: __('merchant_panel.settings_saved'));
};

$moveRiderStatus = function (string $key, int $direction): void {
    abort_unless(canStore(StorePermissionEnum::STORE_UPDATE->value), 403);

    app(StoreStatusService::class)->moveRider((string) $this->storeId, $key, $direction);

    $this->loadRider();

    $this->dispatch('swal', type: 'success', title: __('merchant_panel.settings_saved'));
};

$addConfirmationStatus = function (): void {
    abort_unless(canStore(StorePermissionEnum::STORE_UPDATE->value), 403);

    try {
        app(StoreStatusService::class)->addStatus(
            (string) $this->storeId,
            StoreStatusService::TYPE,
            $this->newConfirmationLabel,
            $this->newConfirmationColor,
            $this->newConfirmationLinkedTo,
        );

        $this->newConfirmationLabel = '';
        $this->newConfirmationColor = 'gray';
        $this->showAddConfirmation = false;
        $this->loadConfirmation();

        $this->dispatch('swal', type: 'success', title: __('merchant_panel.settings_saved'));
    } catch (Throwable $e) {
        $this->dispatch('swal', type: 'error', title: $e->getMessage());
    }
};

$addRiderStatus = function (): void {
    abort_unless(canStore(StorePermissionEnum::STORE_UPDATE->value), 403);

    try {
        app(StoreStatusService::class)->addStatus(
            (string) $this->storeId,
            StoreStatusService::TRACKING_TYPE,
            $this->newRiderLabel,
            $this->newRiderColor,
        );

        $this->newRiderLabel = '';
        $this->newRiderColor = 'gray';
        $this->showAddRider = false;
        $this->loadRider();

        $this->dispatch('swal', type: 'success', title: __('merchant_panel.settings_saved'));
    } catch (Throwable $e) {
        $this->dispatch('swal', type: 'error', title: $e->getMessage());
    }
};

$deleteConfirmationStatus = function (string $key): void {
    abort_unless(canStore(StorePermissionEnum::STORE_UPDATE->value), 403);

    try {
        app(StoreStatusService::class)->deleteStatus((string) $this->storeId, StoreStatusService::TYPE, $key);
        $this->loadConfirmation();

        $this->dispatch('swal', type: 'success', title: __('merchant_panel.settings_saved'));
    } catch (Throwable $e) {
        $this->dispatch('swal', type: 'error', title: $e->getMessage());
    }
};

$deleteRiderStatus = function (string $key): void {
    abort_unless(canStore(StorePermissionEnum::STORE_UPDATE->value), 403);

    try {
        app(StoreStatusService::class)->deleteStatus((string) $this->storeId, StoreStatusService::TRACKING_TYPE, $key);
        $this->loadRider();

        $this->dispatch('swal', type: 'success', title: __('merchant_panel.settings_saved'));
    } catch (Throwable $e) {
        $this->dispatch('swal', type: 'error', title: $e->getMessage());
    }
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

                    <div class="flex gap-1">
                        @if ($confirmationView === 'customize')
                            <button type="button" wire:click="saveChanges" wire:loading.attr="disabled"
                                class="edz-btn edz-btn--primary edz-btn--sm">
                                <x-edz.icon name="check-circle" class="w-4 h-4" />
                                {{ __('merchant_panel.save') }}
                            </button>
                        @endif
                        <button type="button" wire:click="$set('showAddConfirmation', true)"
                            class="edz-btn edz-btn--ghost edz-btn--sm">
                            <x-edz.icon name="plus" class="w-4 h-4" />
                            {{ __('merchant_panel.confirmation_add') }}
                        </button>
                    </div>
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
                                        <th class="w-14"></th>
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
                                                    @if ($row['is_custom'])
                                                        <span class="edz-badge edz-badge--warning">{{ __('merchant_panel.status_custom') }}</span>
                                                        @if ($row['linked_to'])
                                                            <span class="edz-badge edz-badge--neutral" title="{{ __('merchant_panel.status_linked_to') }}">
                                                                <x-edz.icon name="link" class="w-3 h-3" />
                                                                {{ $this->resolve($row['linked_to'])->label }}
                                                            </span>
                                                        @endif
                                                    @elseif ($row['has_override'])
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
                                            <td>
                                                @if ($row['is_custom'])
                                                    <button type="button" wire:click="deleteConfirmationStatus('{{ $row['key'] }}')"
                                                        wire:confirm="{{ __('merchant_panel.confirm_delete_status') }}"
                                                        class="edz-btn edz-btn--ghost edz-btn--icon text-red-500"
                                                        title="{{ __('merchant_panel.status_delete') }}">
                                                        <x-edz.icon name="trash" class="w-4 h-4" />
                                                    </button>
                                                @endif
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
                                    @if ($row['is_custom'] && $row['linked_to'])
                                        <span class="edz-badge edz-badge--neutral" title="{{ __('merchant_panel.status_linked_to') }}">
                                            <x-edz.icon name="link" class="w-3 h-3" />
                                            {{ $this->resolve($row['linked_to'])->label }}
                                        </span>
                                    @endif
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
                                        @if ($row['is_custom'])
                                            <button type="button" wire:click="deleteConfirmationStatus('{{ $row['key'] }}')"
                                                wire:confirm="{{ __('merchant_panel.confirm_delete_status') }}"
                                                class="edz-btn edz-btn--ghost edz-btn--icon text-red-500"
                                                title="{{ __('merchant_panel.status_delete') }}">
                                                <x-edz.icon name="trash" class="w-4 h-4" />
                                            </button>
                                        @endif
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
                                    <th>{{ __('merchant_panel.carrier_tracking_meaning') }}</th>
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
                                        <td class="text-sm text-ink-muted max-w-md">{{ $row['meaning'] }}</td>
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
            <div class="space-y-4">
                <div class="flex items-center justify-between gap-3 flex-wrap">
                    <div class="flex gap-1">
                        <button type="button" wire:click="setRiderView('customize')"
                            class="edz-btn edz-btn--sm {{ $riderView === 'customize' ? 'edz-btn--primary' : 'edz-btn--ghost' }}">
                            <x-edz.icon name="adjustments" class="w-4 h-4" />
                            {{ __('merchant_panel.rider_customize') }}
                        </button>
                        <button type="button" wire:click="setRiderView('order')"
                            class="edz-btn edz-btn--sm {{ $riderView === 'order' ? 'edz-btn--primary' : 'edz-btn--ghost' }}">
                            <x-edz.icon name="arrow-up" class="w-4 h-4 rotate-90" />
                            {{ __('merchant_panel.rider_reorder') }}
                        </button>
                    </div>

                    <div class="flex gap-1">
                        @if ($riderView === 'customize')
                            <button type="button" wire:click="saveRiderChanges" wire:loading.attr="disabled"
                                class="edz-btn edz-btn--primary edz-btn--sm">
                                <x-edz.icon name="check-circle" class="w-4 h-4" />
                                {{ __('merchant_panel.save') }}
                            </button>
                        @endif
                        <button type="button" wire:click="$set('showAddRider', true)"
                            class="edz-btn edz-btn--ghost edz-btn--sm">
                            <x-edz.icon name="plus" class="w-4 h-4" />
                            {{ __('merchant_panel.rider_add') }}
                        </button>
                    </div>
                </div>

                @if ($riderView === 'customize')
                    <div class="edz-card edz-card--padded">
                        <div class="overflow-x-auto">
                            <table class="edz-table">
                                <thead>
                                    <tr>
                                        <th class="w-14">{{ __('merchant_panel.status_position') }}</th>
                                        <th>{{ __('merchant_panel.status') }}</th>
                                        <th>{{ __('merchant_panel.status_label') }}</th>
                                        <th class="w-48">{{ __('merchant_panel.status_color') }}</th>
                                        <th class="w-14"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($riderStatusList as $index => $row)
                                        @php $resolved = $this->resolveTracking($row['key']); @endphp
                                        <tr wire:key="rider-row-{{ $row['key'] }}">
                                            <td class="text-ink-muted font-mono text-xs">{{ $index + 1 }}</td>
                                            <td>
                                                <div class="flex items-center gap-2">
                                                    <span
                                                        class="inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-0.5 rounded-full {{ $resolved->classes() }}">
                                                        {{ $resolved->label }}
                                                    </span>
                                                    @if ($row['is_custom'])
                                                        <span class="edz-badge edz-badge--warning">{{ __('merchant_panel.status_custom') }}</span>
                                                    @elseif ($row['has_override'])
                                                        <span class="edz-badge edz-badge--neutral">{{ __('merchant_panel.status_custom') }}</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <input type="text" maxlength="255"
                                                    wire:model.defer="riderLabels.{{ $row['key'] }}"
                                                    placeholder="{{ $resolved->label }}"
                                                    class="edz-input edz-input--sm w-full" />
                                            </td>
                                            <td>
                                                <select wire:model.defer="riderColors.{{ $row['key'] }}"
                                                    class="edz-input edz-input--sm w-full">
                                                    @foreach ($this->colorOptions() as $variant)
                                                        <option value="{{ $variant }}">{{ __('merchant_panel.color_'.$variant) }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                @if ($row['is_custom'])
                                                    <button type="button" wire:click="deleteRiderStatus('{{ $row['key'] }}')"
                                                        wire:confirm="{{ __('merchant_panel.confirm_delete_status') }}"
                                                        class="edz-btn edz-btn--ghost edz-btn--icon text-red-500"
                                                        title="{{ __('merchant_panel.status_delete') }}">
                                                        <x-edz.icon name="trash" class="w-4 h-4" />
                                                    </button>
                                                @endif
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
                            @foreach ($riderStatusList as $index => $row)
                                @php $resolved = $this->resolveTracking($row['key']); @endphp
                                <li wire:key="rider-order-{{ $row['key'] }}"
                                    class="flex items-center gap-3 py-3">
                                    <span class="edz-badge edz-badge--neutral font-mono">{{ $index + 1 }}</span>
                                    <span
                                        class="inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-0.5 rounded-full {{ $resolved->classes() }}">
                                        {{ $resolved->label }}
                                    </span>
                                    <div class="ms-auto flex items-center gap-1">
                                        <button type="button" wire:click="moveRiderStatus('{{ $row['key'] }}', -1)"
                                            @disabled($index === 0) title="{{ __('merchant_panel.move_up') }}"
                                            class="edz-btn edz-btn--ghost edz-btn--icon disabled:opacity-30">
                                            <x-edz.icon name="arrow-up" class="w-4 h-4" />
                                        </button>
                                        <button type="button" wire:click="moveRiderStatus('{{ $row['key'] }}', 1)"
                                            @disabled($index === count($riderStatusList) - 1) title="{{ __('merchant_panel.move_down') }}"
                                            class="edz-btn edz-btn--ghost edz-btn--icon disabled:opacity-30">
                                            <x-edz.icon name="arrow-down" class="w-4 h-4" />
                                        </button>
                                        @if ($row['is_custom'])
                                            <button type="button" wire:click="deleteRiderStatus('{{ $row['key'] }}')"
                                                wire:confirm="{{ __('merchant_panel.confirm_delete_status') }}"
                                                class="edz-btn edz-btn--ghost edz-btn--icon text-red-500"
                                                title="{{ __('merchant_panel.status_delete') }}">
                                                <x-edz.icon name="trash" class="w-4 h-4" />
                                            </button>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        @endif

        {{-- Add confirmation status modal --}}
        @if ($showAddConfirmation)
            <div x-data x-on:keydown.escape.window="$wire.set('showAddConfirmation', false)"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                <div class="edz-card w-full max-w-md" @click.outside="$wire.set('showAddConfirmation', false)">
                    <div class="edz-card__header">
                        <h3 class="edz-card__title">{{ __('merchant_panel.confirmation_add_title') }}</h3>
                    </div>
                    <div class="edz-card__body space-y-4">
                        <div>
                            <label class="edz-label">{{ __('merchant_panel.confirmation_add_label') }}</label>
                            <input type="text" maxlength="255" wire:model="newConfirmationLabel"
                                class="edz-input" placeholder="{{ __('merchant_panel.confirmation_add_label_placeholder') }}" />
                        </div>
                        <div>
                            <label class="edz-label">{{ __('merchant_panel.confirmation_add_color') }}</label>
                            <select wire:model="newConfirmationColor" class="edz-input">
                                @foreach ($this->colorOptions() as $variant)
                                    <option value="{{ $variant }}">{{ __('merchant_panel.color_'.$variant) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="edz-label">{{ __('merchant_panel.confirmation_add_linked_to') }}</label>
                            <select wire:model="newConfirmationLinkedTo" class="edz-input">
                                @foreach ($this->confirmationOptions() as $option)
                                    <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-ink-muted">{{ __('merchant_panel.confirmation_add_linked_hint') }}</p>
                        </div>
                    </div>
                    <div class="edz-card__footer flex justify-end gap-2">
                        <button wire:click="$set('showAddConfirmation', false)" class="edz-btn edz-btn--ghost">
                            {{ __('buttons.cancel') }}
                        </button>
                        <button wire:click="addConfirmationStatus" class="edz-btn edz-btn--primary">
                            {{ __('merchant_panel.confirmation_add_submit') }}
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- Add rider status modal --}}
        @if ($showAddRider)
            <div x-data x-on:keydown.escape.window="$wire.set('showAddRider', false)"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                <div class="edz-card w-full max-w-md" @click.outside="$wire.set('showAddRider', false)">
                    <div class="edz-card__header">
                        <h3 class="edz-card__title">{{ __('merchant_panel.rider_add_title') }}</h3>
                    </div>
                    <div class="edz-card__body space-y-4">
                        <div>
                            <label class="edz-label">{{ __('merchant_panel.rider_add_label') }}</label>
                            <input type="text" maxlength="255" wire:model="newRiderLabel"
                                class="edz-input" placeholder="{{ __('merchant_panel.rider_add_label_placeholder') }}" />
                        </div>
                        <div>
                            <label class="edz-label">{{ __('merchant_panel.rider_add_color') }}</label>
                            <select wire:model="newRiderColor" class="edz-input">
                                @foreach ($this->colorOptions() as $variant)
                                    <option value="{{ $variant }}">{{ __('merchant_panel.color_'.$variant) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="edz-card__footer flex justify-end gap-2">
                        <button wire:click="$set('showAddRider', false)" class="edz-btn edz-btn--ghost">
                            {{ __('buttons.cancel') }}
                        </button>
                        <button wire:click="addRiderStatus" class="edz-btn edz-btn--primary">
                            {{ __('merchant_panel.rider_add_submit') }}
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>