<?php

namespace App\Livewire\Concerns;

use App\Domains\Order\Support\AssignmentCandidateResolver;
use App\Domains\Order\Services\OrderTrackingAssignmentService;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Orders\OrderTracking;
use App\Models\Stores\Team\StoreMembership;

trait TrackingReassignConcern
{
    public function openTrackingReassignModal(string $trackingId): void
    {
        if (! canStore(StorePermissionEnum::ORDER_MANAGE->value)) {
            $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('messages.permission_denied')]);
            return;
        }

        $this->trackingReassignCandidates = app(AssignmentCandidateResolver::class)
            ->resolve(currentStoreId(), 'track', StorePermissionEnum::CRM_ORDER_TRACKING->value)
            ->toArray();
        $this->trackingReassignId = $trackingId;
        $this->trackingReassignMembershipId = '';
        $this->trackingReassignOpen = true;
    }

    public function submitTrackingReassign(): void
    {
        if (! canStore(StorePermissionEnum::ORDER_MANAGE->value)) {
            $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('messages.permission_denied')]);
            return;
        }

        if (empty($this->trackingReassignMembershipId)) {
            $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('merchant_panel.select_agent')]);
            return;
        }

        $storeId = currentStoreId();

        $tracking = OrderTracking::where('store_id', $storeId)->findOrFail($this->trackingReassignId);
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

        app(OrderTrackingAssignmentService::class)->reassign($tracking, $targetMembership, $byMembership);

        $this->trackingReassignOpen = false;
        $this->trackingReassignCandidates = [];
        $this->loadShipments();

        $this->dispatch('swal:toast', ['icon' => 'success', 'title' => __('order_flow.tracking_reassigned')]);
    }
}