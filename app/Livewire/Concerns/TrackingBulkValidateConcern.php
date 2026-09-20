<?php

namespace App\Livewire\Concerns;

use App\Domains\Order\Services\OrderTrackingService;
use App\Domains\Shipping\Services\OrderShippingGateway;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Orders\Order;
use App\Models\Orders\OrderTracking;
use Illuminate\Support\Facades\Log;

/**
 * Dispatch-validation on the tracking page (Phase 8).
 *
 * Two entry surfaces, both gated by ORDER_DISPATCH_VALIDATE:
 *
 *  1. The bulk-tasks "اعتماد لدى الناقل" — validates the selected shipments
 *     DIRECTLY (no pre-analysis modal) through the chunked (≤100 / call)
 *     NOEST /valid/orders flow and reports a per-shipment result row (order
 *     number, tracking number, success, failure reason) in a dedicated results
 *     popup. Skipped rows are reported with their own reason.
 *
 *  2. The toolbar scanner / camera modal — every scanned barcode validates
 *     immediately through the single-order flow and appends its own result
 *     row to the in-modal list; no separate "اعتماد" confirmation step.
 */
trait TrackingBulkValidateConcern
{
    /**
     * Open the scanner / camera modal. Selection-agnostic: it validates one
     * scanned tracking at a time, so it also works on an empty page.
     */
    public function openBulkValidateModal(): void
    {
        if (! canStore(StorePermissionEnum::ORDER_DISPATCH_VALIDATE->value)) {
            $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('messages.permission_denied')]);

            return;
        }

        // The scanner surfaces its own results in-modal; a stale results popup
        // from the bulk bar must never stack on top of it.
        $this->showBulkValidateResults = false;
        $this->bulkValidateResults = [];
        $this->bulkValidateBusy = false;

        $this->showBulkValidateModal = true;
    }

    public function closeBulkValidateModal(): void
    {
        $this->showBulkValidateModal = false;
        $this->bulkValidateBusy = false;
    }

    public function closeBulkValidateResults(): void
    {
        $this->showBulkValidateResults = false;
        $this->bulkValidateResults = [];
    }

    /**
     * Bulk bar entry: validate the selected shipments directly, then surface a
     * per-shipment outcome popup (every order + its tracking + success / reason).
     */
    public function runBulkValidate(): void
    {
        if (! canStore(StorePermissionEnum::ORDER_DISPATCH_VALIDATE->value)) {
            $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('messages.permission_denied')]);

            return;
        }

        $orderIds = $this->selectedShipments;

        if ($orderIds === []) {
            return;
        }

        $analysis = $this->buildValidateAnalysis($orderIds);

        $this->showBulkValidateModal = false;
        $this->showBulkValidateResults = true;
        $this->bulkValidateResults = [];
        $this->bulkValidateBusy = true;

        try {
            $this->bulkValidateResults = $this->executeBulkValidation($analysis);

            $this->loadShipments();
            $this->clearSelection();

            $results = $this->bulkValidateResults;
            $done = count(array_filter($results, fn (array $row) => (bool) $row['ok']));
            $failed = count($results) - $done;

            $this->dispatch('swal:toast', [
                'icon' => $failed === 0 ? 'success' : ($done > 0 ? 'warning' : 'error'),
                'title' => $failed === 0
                    ? __('order_flow.bulk_validate_done', ['done' => $done])
                    : __('order_flow.bulk_validate_failed', ['done' => $done, 'failed' => $failed]),
            ]);
        } finally {
            $this->bulkValidateBusy = false;
        }
    }

    /**
     * Classify the given orders as ready / skipped for carrier validation.
     *
     * @param  array<int, string>  $orderIds
     * @return array<int, array{order_id: string, number: string|null, tracking_number: string|null, ready: bool, reasons: array<int, string>}>
     */
    protected function buildValidateAnalysis(array $orderIds): array
    {
        $orders = Order::with(['shippingProvider.carrier'])
            ->where('store_id', currentStoreId())
            ->whereIn('id', $orderIds)
            ->get();

        $analysis = [];
        $trackingService = app(OrderTrackingService::class);

        foreach ($orders as $order) {
            $reasons = [];

            $adapterClass = $order->shippingProvider?->carrier
                ? config(
                    "delivery.carrier_integrations.{$order->shippingProvider->carrier->code}",
                    config('delivery.carrier_integrations.*'),
                )
                : null;

            if (! $order->shipping_provider_id || ! $order->shippingProvider?->carrier) {
                $reasons[] = __('order_flow.validation_carrier_required');
            } elseif (! $adapterClass || ! is_string($adapterClass) || ! class_exists($adapterClass)
                || (! method_exists($adapterClass, 'validateOrder') && ! method_exists($adapterClass, 'validateOrders'))) {
                $reasons[] = __('order_flow.carrier_validation_not_supported');
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
                'tracking_number' => $tracking?->tracking_number,
                'ready' => $reasons === [],
                'reasons' => $reasons,
            ];
        }

        return $analysis;
    }

    /**
     * Run the carrier handover for the ready rows of an analysis and return one
     * outcome row per analyzed shipment (success / carrier reason / skip reason),
     * preserving the analysis order.
     *
     * @param  array<int, array{order_id: string, number: string|null, tracking_number: string|null, ready: bool, reasons: array<int, string>}>  $analysis
     * @return array<int, array{order_id: string, number: string|null, tracking_number: string|null, ok: bool, message: string}>
     */
    protected function executeBulkValidation(array $analysis): array
    {
        $rows = collect($analysis);
        $outcomes = [];

        $orders = Order::with(['shippingProvider.carrier'])
            ->where('store_id', currentStoreId())
            ->whereIn(
                'id',
                $rows->where('ready', true)->pluck('order_id')->filter()->values()->all(),
            )
            ->get()
            ->keyBy(fn (Order $order) => (string) $order->id);

        $gateway = app(OrderShippingGateway::class);
        $membership = currentMembership();

        $byProvider = $rows->where('ready', true)->groupBy(
            fn (array $row) => (string) ($orders[$row['order_id']]->shipping_provider_id ?? ''),
        );

        foreach ($byProvider as $providerRows) {
            $provider = $orders[$providerRows->first()['order_id']]->shippingProvider;

            $adapterClass = $provider?->carrier
                ? config(
                    "delivery.carrier_integrations.{$provider->carrier->code}",
                    config('delivery.carrier_integrations.*'),
                )
                : null;

            if (! $provider || ! $adapterClass || ! is_string($adapterClass) || ! class_exists($adapterClass)) {
                foreach ($providerRows as $row) {
                    $order = $orders[$row['order_id']];
                    $gateway->recordValidationFailure($order, __('order_flow.carrier_validation_not_supported'));
                    $outcomes[$row['order_id']] = ['ok' => false, 'message' => __('order_flow.carrier_validation_not_supported')];
                }

                continue;
            }

            $adapter = app($adapterClass);

            if (method_exists($adapterClass, 'validateOrders')) {
                // Chunked /valid/orders — NOEST caps each call at 100 trackings.
                // Each chunk is matched back to ITS OWN rows, so a later chunk
                // never re-flags an earlier one (compare orders confirmBulkValidate).
                foreach ($providerRows->values()->chunk(100) as $chunkRows) {
                    $chunkTrackings = $chunkRows
                        ->pluck('tracking_number')
                        ->map('strval')
                        ->values()
                        ->toArray();

                    $result = $adapter->validateOrders($provider, $chunkTrackings);

                    $rejected = $result['failed'] ?? [];
                    $validated = array_fill_keys(array_map('strval', $result['validated'] ?? []), true);

                    foreach ($chunkRows as $row) {
                        $trackingNumber = (string) $row['tracking_number'];
                        $order = $orders[$row['order_id']];

                        if (isset($validated[$trackingNumber])) {
                            try {
                                $gateway->markValidated($order, $membership);
                                $outcomes[$row['order_id']] = ['ok' => true, 'message' => __('order_flow.shipment_validated')];
                            } catch (\Throwable $e) {
                                Log::warning(
                                    "bulk validate persist failed for order [{$row['number']}]: {$e->getMessage()}",
                                );
                                $outcomes[$row['order_id']] = ['ok' => false, 'message' => $e->getMessage()];
                            }
                        } elseif (isset($rejected[$trackingNumber])) {
                            $gateway->recordValidationFailure($order, (string) $rejected[$trackingNumber]);
                            $outcomes[$row['order_id']] = ['ok' => false, 'message' => (string) $rejected[$trackingNumber]];
                        } else {
                            $outcomes[$row['order_id']] = ['ok' => false, 'message' => __('order_flow.shipment_validation_failed')];
                        }
                    }
                }
            } elseif (method_exists($adapterClass, 'validateOrder')) {
                foreach ($providerRows as $row) {
                    $result = $gateway->validate($orders[$row['order_id']], $membership);

                    $ok = (bool) ($result['ok'] ?? false);

                    $outcomes[$row['order_id']] = [
                        'ok' => $ok,
                        'message' => $ok
                            ? (($result['message'] ?? '') ?: __('order_flow.shipment_validated'))
                            : ((string) ($result['message'] ?? $result['error'] ?? __('order_flow.shipment_validation_failed'))),
                    ];
                }
            } else {
                foreach ($providerRows as $row) {
                    $order = $orders[$row['order_id']];
                    $gateway->recordValidationFailure($order, __('order_flow.carrier_validation_not_supported'));
                    $outcomes[$row['order_id']] = ['ok' => false, 'message' => __('order_flow.carrier_validation_not_supported')];
                }
            }
        }

        // Map back over the analysis so skipped rows read naturally in order.
        return $rows->map(function (array $row) use ($outcomes) {
            $outcome = $outcomes[$row['order_id']] ?? null;
            $fallback = implode('، ', $row['reasons'] ?: [__('order_flow.shipment_validation_failed')]);

            return [
                'order_id' => $row['order_id'],
                'number' => $row['number'],
                'tracking_number' => $row['tracking_number'],
                'ok' => $outcome ? (bool) $outcome['ok'] : false,
                'message' => $outcome['message'] ?? $fallback,
            ];
        })->values()->all();
    }

    /**
     * A barcode scan validates immediately through the single-order flow and
     * appends its own outcome to the in-modal results list.
     */
    public function bulkValidateFromBarcode(string $trackingNumber): void
    {
        if (! canStore(StorePermissionEnum::ORDER_DISPATCH_VALIDATE->value)) {
            $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('messages.permission_denied')]);

            return;
        }

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
            $this->appendScanResult(null, $number, false, __('order_flow.shipment_validation_failed'));
            $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('order_flow.shipment_validation_failed')]);

            return;
        }

        $order = Order::with(['shippingProvider.carrier'])
            ->where('store_id', currentStoreId())
            ->find($tracking->order_id);

        if (! $order) {
            $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('order_flow.shipment_validation_failed')]);

            return;
        }

        $result = app(OrderShippingGateway::class)->validate($order, currentMembership());

        $ok = (bool) ($result['ok'] ?? false);
        $message = $ok
            ? (($result['message'] ?? '') ?: __('order_flow.shipment_validated'))
            : ((string) ($result['message'] ?? $result['error'] ?? __('order_flow.shipment_validation_failed')));

        $this->appendScanResult($order->number, $number, $ok, $message);

        $this->loadShipments();

        $this->dispatch('swal:toast', ['icon' => $ok ? 'success' : 'error', 'title' => $message]);
    }

    /**
     * Prepend one scanned outcome to the in-modal results list (newest first).
     */
    protected function appendScanResult(?string $number, ?string $trackingNumber, bool $ok, string $message): void
    {
        $this->bulkValidateResults = collect($this->bulkValidateResults)
            ->prepend([
                'order_id' => null,
                'number' => $number ?? '—',
                'tracking_number' => $trackingNumber ?? '—',
                'ok' => $ok,
                'message' => $message,
            ])
            ->values()
            ->all();
    }
}