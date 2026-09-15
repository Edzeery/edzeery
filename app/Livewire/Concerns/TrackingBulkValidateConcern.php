<?php

namespace App\Livewire\Concerns;

use App\Domains\Order\Services\OrderTrackingService;
use App\Domains\Shipping\Services\OrderShippingGateway;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Orders\Order;
use App\Models\Orders\OrderTracking;

/**
 * Bulk dispatch-validation on the tracking page (Phase 8): a carrier-tab FAB
 * analyzes the current page's shipments, then hands the ready ones over to the
 * carrier through the chunked (≤100 / call) NOEST /valid/orders flow. Every
 * success is persisted via the gateway and audited as an OrderEvent; the
 * barcode path validates one scanned tracking through the single-order flow.
 */
trait TrackingBulkValidateConcern
{
    public function openBulkValidateModal(): void
    {
        abort_unless(canStore(StorePermissionEnum::ORDER_DISPATCH_VALIDATE->value), 403);

        if (empty($this->shipments)) {
            $this->dispatch('swal:toast', ['icon' => 'warning', 'title' => __('order_flow.bulk_validate_confirm_none')]);

            return;
        }

        $orderIds = collect($this->shipments)->pluck('id')->filter()->values()->toArray();

        if ($orderIds === []) {
            $this->dispatch('swal:toast', ['icon' => 'warning', 'title' => __('order_flow.bulk_validate_confirm_none')]);

            return;
        }

        $orders = Order::with(['shippingProvider.carrier'])
            ->where('store_id', currentStoreId())
            ->whereIn('id', $orderIds)
            ->get();

        $analysis = [];
        $trackingService = app(OrderTrackingService::class);

        foreach ($orders as $order) {
            $reasons = [];

            if (! $order->shipping_provider_id || ! $order->shippingProvider?->carrier) {
                $reasons[] = __('order_flow.validation_carrier_required');
            } else {
                $adapterClass = config(
                    "delivery.carrier_integrations.{$order->shippingProvider->carrier->code}",
                    config('delivery.carrier_integrations.*'),
                );

                if (! $adapterClass || ! is_string($adapterClass) || ! class_exists($adapterClass) || ! method_exists($adapterClass, 'validateOrder')) {
                    $reasons[] = __('order_flow.carrier_validation_not_supported');
                }
            }

            $tracking = $trackingService->currentTracking($order);

            if (! $tracking?->tracking_number) {
                $reasons[] = __('order_flow.validation_tracking_required');
            } elseif ($tracking->isCarrierValidated()) {
                $reasons[] = __('order_flow.shipment_already_validated');
            }

            $analysis[] = [
                'order_id' => (string) $order->id,
                'number' => $order->number,
                'provider' => $order->shippingProvider?->name ?? '—',
                'tracking_number' => $tracking?->tracking_number,
                'ready' => $reasons === [],
                'reasons' => $reasons,
            ];
        }

        $this->bulkValidateAnalysis = $analysis;
        $this->bulkValidateReadyCount = collect($analysis)->where('ready', true)->count();
        $this->bulkValidateSkipCount = count($analysis) - $this->bulkValidateReadyCount;

        $this->showBulkValidateModal = true;
    }

    public function closeBulkValidateModal(): void
    {
        $this->showBulkValidateModal = false;
        $this->bulkValidateAnalysis = [];
        $this->bulkValidateReadyCount = 0;
        $this->bulkValidateSkipCount = 0;
        $this->bulkValidateBusy = false;
    }

    public function confirmBulkValidate(): void
    {
        abort_unless(canStore(StorePermissionEnum::ORDER_DISPATCH_VALIDATE->value), 403);

        $analysis = $this->bulkValidateAnalysis;
        $this->bulkValidateAnalysis = [];
        $this->bulkValidateReadyCount = 0;
        $this->bulkValidateSkipCount = 0;
        $this->showBulkValidateModal = false;

        $ready = collect($analysis)->where('ready', true)->values();

        if ($ready->isEmpty()) {
            return;
        }

        $this->bulkValidateBusy = true;

        try {
            $orderIds = $ready->pluck('order_id')->filter()->values()->toArray();

            if ($orderIds === []) {
                return;
            }

            $orders = Order::with(['shippingProvider.carrier'])
                ->where('store_id', currentStoreId())
                ->whereIn('id', $orderIds)
                ->get()
                ->keyBy(fn ($order) => (string) $order->id);

            $gateway = app(OrderShippingGateway::class);
            $membership = currentMembership();

            $done = 0;
            $failed = 0;

            $byProvider = $ready->groupBy(fn ($row) => (string) $orders[$row['order_id']]->shipping_provider_id);

            foreach ($byProvider as $rows) {
                $first = $orders[$rows->first()['order_id']];
                $provider = $first->shippingProvider;

                $adapterClass = $provider?->carrier
                    ? config(
                        "delivery.carrier_integrations.{$provider->carrier->code}",
                        config('delivery.carrier_integrations.*'),
                    )
                    : null;

                if (! $provider || ! $adapterClass || ! is_string($adapterClass) || ! class_exists($adapterClass) || ! method_exists($adapterClass, 'validateOrders')) {
                    foreach ($rows as $row) {
                        $gateway->recordValidationFailure($orders[$row['order_id']], __('order_flow.carrier_validation_not_supported'));
                        $failed++;
                    }

                    continue;
                }

                $adapter = app($adapterClass);

                // Chunked /valid/orders — NOEST caps each call at 100 trackings.
                foreach (array_chunk($rows->pluck('tracking_number')->map('strval')->values()->toArray(), 100, true) as $chunk) {
                    $result = $adapter->validateOrders($provider, $chunk);

                    $rejected = $result['failed'] ?? [];
                    $validated = array_fill_keys(array_map('strval', $result['validated'] ?? []), true);

                    foreach ($rows as $row) {
                        $trackingNumber = (string) $row['tracking_number'];

                        if (isset($validated[$trackingNumber])) {
                            try {
                                $gateway->markValidated($orders[$row['order_id']], $membership);
                                $done++;
                            } catch (\Throwable $e) {
                                \Illuminate\Support\Facades\Log::warning(
                                    "bulk validate persist failed for order [{$row['number']}]: {$e->getMessage()}"
                                );
                                $failed++;
                            }
                        } elseif (isset($rejected[$trackingNumber])) {
                            $gateway->recordValidationFailure($orders[$row['order_id']], (string) $rejected[$trackingNumber]);
                            $failed++;
                        }
                    }
                }
            }

            $this->loadShipments();

            if ($done > 0 || $failed > 0) {
                $this->dispatch('swal:toast', [
                    'icon' => $failed === 0 ? 'success' : 'warning',
                    'title' => $failed === 0
                        ? __('order_flow.bulk_validate_done', ['done' => $done])
                        : __('order_flow.bulk_validate_failed', ['done' => $done, 'failed' => $failed]),
                ]);
            }
        } finally {
            $this->bulkValidateBusy = false;
        }
    }

    public function bulkValidateFromBarcode(string $trackingNumber): void
    {
        abort_unless(canStore(StorePermissionEnum::ORDER_DISPATCH_VALIDATE->value), 403);

        $number = trim($trackingNumber);

        if ($number === '') {
            $this->dispatch('swal:toast', ['icon' => 'warning', 'title' => __('order_flow.validation_tracking_required')]);

            return;
        }

        $tracking = OrderTracking::where('store_id', currentStoreId())
            ->where('tracking_number', $number)
            ->latest('id')
            ->first();

        if (! $tracking) {
            $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('order_flow.shipment_validation_failed')]);

            return;
        }

        $order = Order::with(['shippingProvider.carrier'])
            ->where('store_id', currentStoreId())
            ->find($tracking->order_id);

        if (! $order) {
            return;
        }

        $result = app(OrderShippingGateway::class)->validate($order, currentMembership());

        if (! ($result['ok'] ?? false)) {
            $this->dispatch('swal:toast', [
                'icon' => 'error',
                'title' => ($result['message'] ?? $result['error'] ?? __('order_flow.shipment_validation_failed')),
            ]);

            return;
        }

        $this->bulkValidateAnalysis = collect($this->bulkValidateAnalysis)
            ->map(function (array $row) use ($order) {
                if ($row['order_id'] === (string) $order->id) {
                    $row['ready'] = false;
                }

                return $row;
            })
            ->values()
            ->toArray();

        $this->bulkValidateReadyCount = collect($this->bulkValidateAnalysis)->where('ready', true)->count();
        $this->bulkValidateSkipCount = count($this->bulkValidateAnalysis) - $this->bulkValidateReadyCount;

        $this->dispatch('swal:toast', [
            'icon' => 'success',
            'title' => ($result['message'] ?: __('order_flow.shipment_validated')),
        ]);
    }
}