<?php

use App\Domains\Order\Services\OrderService;
use App\Domains\Order\Services\ReturnVerificationService;
use App\Enums\Store\ReturnInspectionResult;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Orders\OrderTracking;
use App\Models\Status;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;

layout('components.layouts.store');

state([
    'returnTab' => 'awaiting_verification',
    'trackings' => [],
    'tabCounts' => ['awaiting_verification' => 0, 'awaiting_processing' => 0, 'processed' => 0],
    'page' => 1,
    'perPage' => 25,
    'scanCode' => '',
    'processTrackingId' => '',
    'processResult' => 'good',
    'processNotes' => '',
    'showProcessModal' => false,
]);

mount(function (): void {
    abort_unless(
        canStore(StorePermissionEnum::RETURNS_VERIFY_BARCODE->value),
        403
    );

    $this->loadTrackings();
});

$loadTrackings = function (): void {
    $storeId = currentStoreId();

    // Per-tab COUNTs stay cheap; only the active tab's rows are hydrated, and
    // paginated — instead of loading the store's entire returned history on
    // every mount/navigation.
    $base = fn () => OrderTracking::where('store_id', $storeId)->whereNotNull('returned_at');

    $this->tabCounts = [
        'awaiting_verification' => (clone $base())->whereNull('verified_at')->count(),
        'awaiting_processing' => (clone $base())->whereNotNull('verified_at')->whereNull('processed_at')->count(),
        'processed' => (clone $base())->whereNotNull('processed_at')->count(),
    ];

    $query = (clone $base())
        ->with(['order.customer', 'order.status', 'order.latestTracking.shippingProvider'])
        ->orderByDesc('returned_at');

    $query = match ($this->returnTab) {
        'awaiting_verification' => $query->whereNull('verified_at'),
        'awaiting_processing' => $query->whereNotNull('verified_at')->whereNull('processed_at'),
        'processed' => $query->whereNotNull('processed_at'),
        default => $query,
    };

    $this->trackings = $query
        ->simplePaginate($this->perPage, ['*'], 'page', $this->page)
        ->getCollection()
        ->values()
        ->toArray();
};

$nextPage = function (): void {
    $this->page++;
    $this->loadTrackings();
};

$previousPage = function (): void {
    if ($this->page > 1) {
        $this->page--;
        $this->loadTrackings();
    }
};

$changeTab = function (string $tab): void {
    if (! in_array($tab, ['awaiting_verification', 'awaiting_processing', 'processed'], true)) {
        return;
    }

    $this->returnTab = $tab;
    $this->page = 1;
    $this->loadTrackings();
};

$filteredTrackings = function (): array {
    return $this->trackings;
};

$verifyScan = function (string $code): void {
    if (! canStore(StorePermissionEnum::RETURNS_VERIFY_BARCODE->value)) {
        return;
    }

    $service = app(ReturnVerificationService::class);
    $membership = \App\Models\Stores\Team\StoreMembership::where('store_id', currentStoreId())
        ->where('user_id', auth()->id())
        ->first();

    $tracking = $service->verifyByCode(currentStoreId(), $code, $membership);

    if (! $tracking) {
        $this->dispatch('swal:toast', [
            'icon' => 'error',
            'title' => __('merchant_panel.barcode_not_found'),
        ]);
        return;
    }

    $this->scanCode = '';
    $this->loadTrackings();

    $this->dispatch('swal:toast', [
        'icon' => 'success',
        'title' => __('merchant_panel.verified_order', ['number' => $tracking->order->number ?? '']),
    ]);
};

$openProcessModal = function (string $trackingId): void {
    if (! canStore(StorePermissionEnum::RETURNS_PROCESS->value)) {
        return;
    }

    $this->processTrackingId = $trackingId;
    $this->processResult = 'good';
    $this->processNotes = '';
    $this->showProcessModal = true;
};

$submitProcess = function (): void {
    if (! canStore(StorePermissionEnum::RETURNS_PROCESS->value)) {
        return;
    }

    $tracking = OrderTracking::findOrFail($this->processTrackingId);
    $membership = \App\Models\Stores\Team\StoreMembership::where('store_id', currentStoreId())
        ->where('user_id', auth()->id())
        ->first();

    $result = ReturnInspectionResult::tryFrom($this->processResult);
    if (! $result) {
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => 'Invalid inspection result.']);
        return;
    }

    $service = app(ReturnVerificationService::class);
    $service->process($tracking, $result, $this->processNotes, $membership);

    $this->showProcessModal = false;
    $this->loadTrackings();

    $this->dispatch('swal:toast', [
        'icon' => 'success',
        'title' => __('merchant_panel.inspection_recorded'),
    ]);
};

$requeue = function (string $trackingId): void {
    if (! canStore(StorePermissionEnum::RETURNS_PROCESS->value)) {
        return;
    }

    $tracking = OrderTracking::findOrFail($trackingId);
    $membership = \App\Models\Stores\Team\StoreMembership::where('store_id', currentStoreId())
        ->where('user_id', auth()->id())
        ->first();

    $service = app(ReturnVerificationService::class);
    $service->requeue($tracking, $membership);

    $this->loadTrackings();

    $this->dispatch('swal:toast', [
        'icon' => 'success',
        'title' => __('merchant_panel.order_requeued'),
    ]);
};
?>

<div>
    {{-- Scan Input --}}
    <div class="mb-6">
        <div class="edz-card">
            <div class="edz-card__body">
                <label class="edz-label">{{ __('merchant_panel.scan_return_barcode') }}</label>
                <input
                    type="text"
                    wire:model.live="scanCode"
                    @keydown.enter.prevent="$wire.verifyScan($event.target.value); $event.target.value = ''"
                    class="edz-input"
                    placeholder="{{ __('merchant_panel.scan_return_barcode_placeholder') }}"
                    autofocus
                />
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="mb-4 flex gap-2">
        <button
            wire:click="changeTab('awaiting_verification')"
            class="edz-btn {{ $returnTab === 'awaiting_verification' ? 'edz-btn--primary' : 'edz-btn--ghost' }}"
        >
            {{ __('merchant_panel.awaiting_verification') }}
            <span class="ml-1 text-xs opacity-60">
                ({{ $tabCounts['awaiting_verification'] }})
            </span>
        </button>
        <button
            wire:click="changeTab('awaiting_processing')"
            class="edz-btn {{ $returnTab === 'awaiting_processing' ? 'edz-btn--primary' : 'edz-btn--ghost' }}"
        >
            {{ __('merchant_panel.awaiting_processing') }}
            <span class="ml-1 text-xs opacity-60">
                ({{ $tabCounts['awaiting_processing'] }})
            </span>
        </button>
        <button
            wire:click="changeTab('processed')"
            class="edz-btn {{ $returnTab === 'processed' ? 'edz-btn--primary' : 'edz-btn--ghost' }}"
        >
            {{ __('merchant_panel.processed') }}
            <span class="ml-1 text-xs opacity-60">
                ({{ $tabCounts['processed'] }})
            </span>
        </button>
    </div>

    {{-- Tracking List --}}
    <div class="edz-card">
        <div class="edz-card__body p-0">
            @php $filtered = $this->filteredTrackings(); @endphp

            @if (empty($filtered))
                <div class="p-8 text-center text-gray-500">
                    {{ __('merchant_panel.no_returns_in_tab') }}
                </div>
            @else
                <div class="divide-y">
                    @foreach ($filtered as $t)
                        @php
                            $order = $t['order'] ?? [];
                            $tracking = $order['latest_tracking'] ?? null;
                        @endphp
                        <div class="p-4 flex items-center justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-gray-900">
                                        #{{ $order['number'] ?? '' }}
                                    </span>
                                    @if ($tracking)
                                        <x-status-badge domain="general" :status="$tracking['status_key'] ?? ''" />
                                    @endif
                                </div>
                                <div class="text-sm text-gray-500 mt-1">
                                    {{ $order['customer']['name'] ?? '' }} — {{ $order['customer']['phone'] ?? '' }}
                                </div>
                                <div class="text-xs text-gray-400 mt-1">
                                    {{ __('merchant_panel.returned_at') }}: {{ $t['returned_at'] ?? '' }}
                                </div>
                            </div>

                            <div class="flex items-center gap-2 shrink-0">
                                {{-- Verification badge --}}
                                @if ($t['verified_at'])
                                    <span class="edz-badge edz-badge--success">{{ __('merchant_panel.verified') }}</span>
                                @else
                                    <span class="edz-badge edz-badge--warning">{{ __('merchant_panel.unverified') }}</span>
                                @endif

                                {{-- Process badge --}}
                                @if ($t['processed_at'])
                                    @php $inspResult = \App\Enums\Store\ReturnInspectionResult::tryFrom($t['inspection_result'] ?? ''); @endphp
                                    @if ($inspResult)
                                        <span class="edz-badge edz-badge--{{ $inspResult->isRequeueEligible() ? 'success' : 'danger' }}">
                                            {{ $inspResult->label() }}
                                        </span>
                                    @endif
                                @endif

                                {{-- Process button --}}
                                @if (! $t['processed_at'] && canStore(\App\Enums\Store\StorePermissionEnum::RETURNS_PROCESS->value))
                                    <button
                                        wire:click="openProcessModal('{{ $t['id'] }}')"
                                        class="edz-btn edz-btn--primary edz-btn--sm"
                                    >
                                        {{ __('merchant_panel.process') }}
                                    </button>
                                @endif

                                {{-- Requeue button --}}
                                @if (
                                    $t['processed_at']
                                    && ($t['inspection_result'] ?? '') === 'good'
                                    && empty($t['requeued_at'])
                                    && canStore(\App\Enums\Store\StorePermissionEnum::RETURNS_PROCESS->value)
                                )
                                    <button
                                        x-data
                                        @click.prevent="(async () => { if (await EdzSwal.confirmDelete('{{ $order['number'] ?? '' }}')) $wire.requeue('{{ $t['id'] }}') })()"
                                        class="edz-btn edz-btn--primary edz-btn--sm"
                                    >
                                        {{ __('merchant_panel.requeue') }}
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Pagination --}}
            @if (count($this->trackings) >= $perPage)
                <div class="flex items-center justify-end gap-2 p-4 border-t">
                    <button
                        wire:click="previousPage"
                        {{ $page <= 1 ? 'disabled' : '' }}
                        class="edz-btn edz-btn--ghost edz-btn--sm"
                    >
                        {{ __('buttons.previous') }}
                    </button>
                    <span class="text-sm text-gray-500">
                        {{ __('titles.page') }} {{ $page }}
                    </span>
                    <button
                        wire:click="nextPage"
                        class="edz-btn edz-btn--primary edz-btn--sm"
                    >
                        {{ __('buttons.next') }}
                    </button>
                </div>
            @endif
        </div>
    </div>

    {{-- Process Modal --}}
    @if ($showProcessModal)
        <div
            x-data
            x-on:keydown.escape.window="$wire.set('showProcessModal', false)"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
        >
            <div class="edz-card w-full max-w-md" @click.outside="$wire.set('showProcessModal', false)">
                <div class="edz-card__header">
                    <h3 class="edz-card__title">{{ __('merchant_panel.inspection') }}</h3>
                </div>
                <div class="edz-card__body space-y-4">
                    {{-- Result radios --}}
                    <div>
                        <label class="edz-label">{{ __('merchant_panel.inspection_result') }}</label>
                        <div class="space-y-2">
                            @foreach (\App\Enums\Store\ReturnInspectionResult::cases() as $result)
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input
                                        type="radio"
                                        wire:model.live="processResult"
                                        value="{{ $result->value }}"
                                        class="edz-radio"
                                    />
                                    <span>{{ $result->label() }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Notes --}}
                    <div>
                        <label class="edz-label">{{ __('merchant_panel.inspection_notes') }}</label>
                        <textarea
                            wire:model.live="processNotes"
                            class="edz-textarea"
                            rows="3"
                            placeholder="{{ __('merchant_panel.inspection_notes_placeholder') }}"
                        ></textarea>
                    </div>
                </div>
                <div class="edz-card__footer flex justify-end gap-2">
                    <button
                        wire:click="$set('showProcessModal', false)"
                        class="edz-btn edz-btn--ghost"
                    >
                        {{ __('buttons.cancel') }}
                    </button>
                    <button
                        wire:click="submitProcess"
                        class="edz-btn edz-btn--primary"
                    >
                        {{ __('merchant_panel.save_inspection') }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
