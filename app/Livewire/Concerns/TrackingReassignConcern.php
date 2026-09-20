<?php

namespace App\Livewire\Concerns;

use App\Domains\Order\Support\AssignmentCandidateResolver;
use App\Domains\Order\Services\OrderTrackingAssignmentService;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Orders\OrderTracking;
use App\Models\Stores\Team\StoreMembership;

trait TrackingReassignConcern
{
    public bool $trackingReassignBulk = false;

    /**
     * Open the internal-team reassign modal for a single order row. The incoming
     * id may be the order row id (tracking grid) or a tracking id — resolve the
     * latest tracking either way so submitTrackingReassign() always works.
     */
    public function openTrackingReassignModal(string $rowId): void
    {
        if (! canReassignOrders()) {
            $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('messages.permission_denied')]);
            return;
        }

        $storeId = currentStoreId();

        $tracking = OrderTracking::where('store_id', $storeId)->where('id', $rowId)->first()
            ?? OrderTracking::where('store_id', $storeId)->where('order_id', $rowId)->latest('id')->first();

        if (! $tracking) {
            $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('order_flow.reassign_no_tracking')]);
            return;
        }

        $this->trackingReassignBulk = false;
        $this->trackingReassignCandidates = app(AssignmentCandidateResolver::class)
            ->resolve($storeId, 'track', StorePermissionEnum::CRM_ORDER_TRACKING->value)
            ->toArray();
        $this->trackingReassignId = $tracking->id;
        $this->trackingReassignMembershipId = '';
        $this->trackingReassignOpen = true;
    }

    /**
     * Open the reassign modal for every currently selected shipment.
     */
    public function openBulkTrackingReassignModal(): void
    {
        if (! canReassignOrders()) {
            $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('messages.permission_denied')]);
            return;
        }

        $storeId = currentStoreId();

        $trackingCount = OrderTracking::where('store_id', $storeId)
            ->whereIn('order_id', $this->selectedShipments)
            ->distinct()
            ->pluck('order_id')
            ->count();

        if (empty($this->selectedShipments) || $trackingCount === 0) {
            $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('order_flow.reassign_no_tracking')]);
            return;
        }

        $this->trackingReassignBulk = true;
        $this->trackingReassignCandidates = app(AssignmentCandidateResolver::class)
            ->resolve($storeId, 'track', StorePermissionEnum::CRM_ORDER_TRACKING->value)
            ->toArray();
        $this->trackingReassignId = null;
        $this->trackingReassignMembershipId = '';
        $this->trackingReassignOpen = true;
    }

    public function submitTrackingReassign(): void
    {
        if (! canReassignOrders()) {
            $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('messages.permission_denied')]);
            return;
        }

        if (empty($this->trackingReassignMembershipId)) {
            $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('merchant_panel.select_agent')]);
            return;
        }

        $storeId = currentStoreId();

        $targetMembership = StoreMembership::where('store_id', $storeId)->findOrFail($this->trackingReassignMembershipId);

        if (! $targetMembership->can(StorePermissionEnum::CRM_ORDER_TRACKING)) {
            $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('messages.permission_denied')]);
            return;
        }

        $byMembership = StoreMembership::where('store_id', $storeId)->where('user_id', auth()->id())->first();

        if (! $byMembership) {
            $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('messages.permission_denied')]);
            return;
        }

        $service = app(OrderTrackingAssignmentService::class);

        if ($this->trackingReassignBulk) {
            $trackings = OrderTracking::where('store_id', $storeId)
                ->whereIn('order_id', $this->selectedShipments)
                ->get()
                ->groupBy('order_id')
                ->map(fn ($rows) => $rows->sortByDesc('id')->first());

            $count = 0;

            foreach ($trackings as $tracking) {
                $service->reassign($tracking, $targetMembership, $byMembership);
                $count++;
            }

            $this->trackingReassignOpen = false;
            $this->trackingReassignCandidates = [];
            $this->clearSelection();
            $this->loadShipments();

            $this->dispatch('swal:toast', [
                'icon' => 'success',
                'title' => __('order_flow.tracking_bulk_reassigned', ['count' => $count]),
            ]);

            return;
        }

        $tracking = OrderTracking::where('store_id', $storeId)->findOrFail($this->trackingReassignId);

        $service->reassign($tracking, $targetMembership, $byMembership);

        $this->trackingReassignOpen = false;
        $this->trackingReassignCandidates = [];
        $this->loadShipments();

        $this->dispatch('swal:toast', ['icon' => 'success', 'title' => __('order_flow.tracking_reassigned')]);
    }
}