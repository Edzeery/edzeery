<?php

use App\Domains\Order\Services\OrderAssignmentService;
use App\Domains\Order\Services\OrderTrackingAssignmentService;
use App\Domains\Order\Support\AssignmentCandidateResolver;
use App\Enums\Store\OrderStatus;
use App\Enums\Store\OrderTrackingStatus;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Orders\Order;
use App\Models\Orders\OrderTracking;
use App\Models\Stores\Team\StoreMembership;
use Livewire\Volt\Component;

use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;
use function Livewire\Volt\uses;

layout('components.layouts.store');

uses([
    \App\Livewire\Concerns\DistributionQueueConcern::class,
]);

state([
    'tab' => 'confirmation',
    'confirmationQueue' => [],
    'trackingQueue' => [],
    'confirmationCount' => 0,
    'trackingCount' => 0,

    // Reassign modal (P34.4) — one shared state set, driven by whichever tab
    // opened it (kind is snapshotted at open time so a tab switch mid-modal
    // cannot rewire the submit to the wrong service).
    'reassignOpen' => false,
    'reassignKind' => null, // 'confirm' | 'track'
    'reassignId' => null,
    'reassignMembershipId' => '',
    'reassignCandidates' => [],
]);

mount(function (): void {
    abort_unless(canStore(StorePermissionEnum::ORDER_MANAGE->value), 403);
    $this->loadQueue();
});

$setTab = function (string $tab): void {
    if (! in_array($tab, ['confirmation', 'tracking'], true)) {
        return;
    }

    $this->tab = $tab;
};

$refresh = function (): void {
    $this->loadQueue();
};

$openReassignModal = function (string $id): void {
    if (! canStore(StorePermissionEnum::ORDER_MANAGE->value)) {
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('messages.permission_denied')]);
        return;
    }

    $isConfirm = $this->tab === 'confirmation';

    $this->reassignCandidates = app(AssignmentCandidateResolver::class)
        ->resolve(
            currentStoreId(),
            $isConfirm ? 'confirm' : 'track',
            $isConfirm ? StorePermissionEnum::ORDER_CONFIRM->value : StorePermissionEnum::CRM_ORDER_TRACKING->value,
        )
        ->toArray();

    $this->reassignKind = $isConfirm ? 'confirm' : 'track';
    $this->reassignId = $id;
    $this->reassignMembershipId = '';
    $this->reassignOpen = true;
};

$submitReassign = function (): void {
    if (! canStore(StorePermissionEnum::ORDER_MANAGE->value)) {
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('messages.permission_denied')]);
        return;
    }

    if (empty($this->reassignMembershipId)) {
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('merchant_panel.select_agent')]);
        return;
    }

    $storeId = currentStoreId();
    $isConfirm = $this->reassignKind === 'confirm';

    if ($isConfirm) {
        $item = Order::where('store_id', $storeId)->findOrFail($this->reassignId);
        $permission = StorePermissionEnum::ORDER_CONFIRM;
    } else {
        $item = OrderTracking::where('store_id', $storeId)->findOrFail($this->reassignId);
        $permission = StorePermissionEnum::CRM_ORDER_TRACKING;
    }

    $targetMembership = StoreMembership::where('store_id', $storeId)->findOrFail($this->reassignMembershipId);
    $byMembership = StoreMembership::where('store_id', $storeId)->where('user_id', auth()->id())->first();

    if (! $byMembership || ! $targetMembership->can($permission)) {
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('messages.permission_denied')]);
        return;
    }

    if ($isConfirm) {
        app(OrderAssignmentService::class)->reassign($item, $targetMembership, $byMembership);
        $toast = __('merchant.order_reassigned');
    } else {
        app(OrderTrackingAssignmentService::class)->reassign($item, $targetMembership, $byMembership);
        $toast = __('order_flow.tracking_reassigned');
    }

    // Manual reassignment is a deliberate within-cap choice that supersedes the
    // overflow flag — clear it so the row leaves the queue. (The reassign services
    // intentionally keep the flag; clearing happens here, on the queue action only.)
    // over_capacity is not a tracked observer field, so no audit noise is created.
    $item->forceFill(['over_capacity' => false])->save();

    $this->reassignOpen = false;
    $this->reassignCandidates = [];
    $this->loadQueue();

    $this->dispatch('swal:toast', ['icon' => 'success', 'title' => $toast]);
};
?>

<div>
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <x-edz.page-header title="{{ __('merchant_panel.order_distribution_queue') }}"
            description="{{ __('merchant_panel.order_distribution_queue_desc') }}">
        </x-edz.page-header>
    </div>

    @include('livewire.merchant.order-distribution-queue.partials.tabs')

    @if ($tab === 'confirmation')
        @include('livewire.merchant.order-distribution-queue.partials.queue-table', ['kind' => 'confirm'])
    @else
        @include('livewire.merchant.order-distribution-queue.partials.queue-table', ['kind' => 'track'])
    @endif

    {{-- Shared reassign modal (P34.4) — same contract as orders and tracking pages. --}}
    @include('livewire.merchant.orders.partials.reassign-modal', [
        'reassignOpen' => $reassignOpen,
        'reassignSubmit' => 'submitReassign',
        'reassignCloseSet' => 'reassignOpen',
        'reassignModel' => 'reassignMembershipId',
        'reassignTargetId' => $reassignMembershipId,
        'reassignCandidates' => $reassignCandidates,
        'reassignTitle' => __('merchant_panel.reassign_order'),
    ])
</div>