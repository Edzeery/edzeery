<?php

namespace App\Livewire\Concerns;

use App\Domains\Shipping\Models\DeliveryRider;
use App\Domains\Shipping\Services\DeliveryRiderService;
use App\Models\Orders\Order;
use App\Models\Orders\OrderTracking;

/**
 * Tracking-grid query, stats, pagination and filters for the merchant tracking
 * page (extracted out of tracking/index.blade.php so the Volt view stays a thin
 * shell of state + orchestration).
 */
trait TrackingGridConcern
{
    // Single source of truth for the shipment query: every filter key (provider,
    // tracking_statuses, created_at date range, amount min/max, city, rider) plus
    // the workflow scope. Grid rows, stat cards and the header-filter dropdown
    // counts all derive from this same filtered query, so every surface responds
    // to the same active filters.
    public function baseTrackingQuery(bool $forAggregate = false): \Illuminate\Database\Eloquent\Builder
    {
        if ($this->showTrash) {
            $query = Order::onlyTrashed()->where('store_id', currentStoreId());

            // Per-tab trash bins: carrier trash keeps only orders sent via a shipping
            // company; rider trash keeps only orders handed to a delivery rider.
            if ($this->trackingTab === 'rider') {
                $query->whereNotNull('delivery_rider_id');
            } else {
                $query->whereNotNull('shipping_provider_id');
            }

            $query->with([
                'customer',
                'status',
                'latestTracking.shippingProvider',
                'shippingProvider.carrier',
                'deliveryRider',
                'city',
                'state',
                'assignedMembership.user',
                'confirmedByHistory.changedBy.user',
                'items.product:id,name',
            ]);

            // Deleted orders can no longer be filtered by an active workflow scope,
            // so the trash list is search-only (search a shipped number, a customer,
            // a tracking number, or a provider name), then ordered by delete time.
            if (! empty($this->search)) {
                $s = trim($this->search);
                $query->where(function ($q) use ($s) {
                    $q->where('number', 'like', "%{$s}%")
                        ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', "%{$s}%"))
                        ->orWhereHas('customer', fn ($cq) => $cq->where('phone', 'like', "%{$s}%"))
                        ->orWhere('phone_secondary', 'like', "%{$s}%")
                        ->orWhereHas('latestTracking', fn ($tq) => $tq->where('tracking_number', 'like', "%{$s}%"))
                        ->orWhereHas('shippingProvider', fn ($pq) => $pq->where('name', 'like', "%{$s}%"));
                });
            }

            if (! $forAggregate) {
                $query->orderByDesc('deleted_at');
            }

            return $query;
        }

        $f = $this->filters;

        $query = Order::query()
            ->where('store_id', currentStoreId());

        if (! $forAggregate) {
            $query->with([
                'customer',
                'status',
                'latestTracking.shippingProvider',
                'shippingProvider.carrier',
                'deliveryRider',
                'city',
                'state',
                'assignedMembership.user',
                'confirmedByHistory.changedBy.user',
                'items.product:id,name',
            ]);
        }

        if (! empty($f['provider'])) {
            $query->where('shipping_provider_id', $f['provider']);
        }

        if (! empty($this->search)) {
            $s = trim($this->search);
            $query->where(function ($q) use ($s) {
                $q->where('number', 'like', "%{$s}%")
                    ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', "%{$s}%"))
                    ->orWhereHas('customer', fn ($cq) => $cq->where('phone', 'like', "%{$s}%"))
                    ->orWhere('phone_secondary', 'like', "%{$s}%")
                    ->orWhereHas('latestTracking', fn ($tq) => $tq->where('tracking_number', 'like', "%{$s}%"))
                    ->orWhereHas('shippingProvider', fn ($pq) => $pq->where('name', 'like', "%{$s}%"));
            });
        }

        if (! empty($f['tracking_statuses'])) {
            $query->whereHas('latestTracking', fn ($q) => $q->whereIn('tracking_status', $f['tracking_statuses']));
        }

        if (filled($f['date_from'] ?? null)) {
            $query->where('created_at', '>=', $f['date_from']);
        }

        if (filled($f['date_to'] ?? null)) {
            $query->where('created_at', '<=', $f['date_to'].' 23:59:59');
        }

        if (filled($f['amount_min'] ?? null) && is_numeric($f['amount_min'])) {
            $query->where('total_amount', '>=', (float) $f['amount_min']);
        }

        if (filled($f['amount_max'] ?? null) && is_numeric($f['amount_max'])) {
            $query->where('total_amount', '<=', (float) $f['amount_max']);
        }

        if (filled($f['city'] ?? null)) {
            $query->where('city_id', $f['city']);
        }

        if (filled($f['state'] ?? null)) {
            $query->where('state_id', $f['state']);
        }

        if (! empty($f['products'])) {
            $query->whereHas('items', fn ($q) => $q->whereIn('product_id', $f['products']));
        }

        if ($this->trackingTab === 'rider') {
            $query->whereNotNull('delivery_rider_id');

            if (filled($f['rider'] ?? null)) {
                $query->where('delivery_rider_id', $f['rider']);
            }

            $membership = $this->getMembership();

            if ($membership && \App\Support\StoreOrderPermissions::isRestrictedMembership($membership)) {
                $query->where('assigned_to_membership_id', $membership->id);
            }
        } else {
            // The carrier tab lists only orders handed to a shipping company —
            // orders sent to a delivery rider belong to the rider tab alone.
            $query->whereNotNull('shipping_provider_id');
        }

        if (filled($f['assigned_to'] ?? null)) {
            $query->where('assigned_to_membership_id', $f['assigned_to']);
        }

        if (filled($f['confirmed_by'] ?? null)) {
            $query->whereHas(
                'confirmedByHistory.changedBy',
                fn ($q) => $q->where('id', $f['confirmed_by']),
            );
        }

        if (($f['can_open'] ?? null) !== null) {
            $query->where('can_open', (bool) $f['can_open']);
        }

        if (($f['send_from_carrier_warehouse'] ?? null) !== null) {
            $query->where('send_from_carrier_warehouse', (bool) $f['send_from_carrier_warehouse']);
        }

        if (($f['shipment_type'] ?? null) !== null) {
            $query->where('shipment_type', $f['shipment_type']);
        }

        // فلترة orders.status_id تنحصر بحالات التوصيل من نوع "order" عبر نقطة الدخول
        // الموحّدة OrderWorkflow::carrierStatusIds() — نوع "tracking" يخص عمود
        // order_trackings.tracking_status ولا يُستخدم هنا (الخلط بينهما يفرّغ التبويبات).
        $trackingStatusIds = \App\Domains\Order\Support\OrderWorkflow::carrierStatusIds();

        if (! empty($trackingStatusIds)) {
            $query->whereIn('status_id', $trackingStatusIds);
        }

        if (! $forAggregate) {
            $query->orderByDesc('created_at');
        }

        return $query;
    }

    public function loadTrackingStats(): void
    {
        if ($this->showTrash) {
            $this->stats = ['active' => 0, 'delivered_today' => 0, 'returned_today' => 0];
            $this->riderRiders = [];
            $this->searchableRiders = [];
            $this->riderStatsActiveCount = 0;
            $this->riderStatsActiveShipments = 0;
            $this->riderStatsCodDueToday = 0;

            return;
        }

        $agg = $this->baseTrackingQuery(true);

        $this->stats = [
            'active' => (clone $agg)
                ->whereDoesntHave('latestTracking', fn ($q) => $q->whereNotNull('delivered_at')->orWhereNotNull('returned_at'))
                ->count(),
            'delivered_today' => (clone $agg)
                ->whereHas('latestTracking', fn ($q) => $q->whereNotNull('delivered_at')->whereDate('delivered_at', today()))
                ->count(),
            'returned_today' => (clone $agg)
                ->whereHas('latestTracking', fn ($q) => $q->whereNotNull('returned_at')->whereDate('returned_at', today()))
                ->count(),
        ];

        $this->riderRiders = [];
        $this->searchableRiders = [];
        $this->riderStatsActiveCount = 0;
        $this->riderStatsActiveShipments = 0;
        $this->riderStatsCodDueToday = 0;

        if ($this->trackingTab !== 'rider') {
            return;
        }

        $openRiderQuery = (clone $agg)
            ->whereNotNull('delivery_rider_id')
            ->whereDoesntHave('latestTracking', fn ($q) => $q->whereNotNull('delivered_at')->orWhereNotNull('returned_at'));

        $this->riderStatsActiveShipments = (clone $openRiderQuery)->count();
        $this->riderStatsCodDueToday = (float) (clone $openRiderQuery)->sum('total_amount');
        $this->riderStatsActiveCount = (clone $openRiderQuery)->distinct()->count('delivery_rider_id');

        $grouped = (clone $agg)
            ->whereNotNull('delivery_rider_id')
            ->selectRaw('delivery_rider_id, count(*) as total_count')
            ->groupBy('delivery_rider_id')
            ->get()
            ->keyBy('delivery_rider_id');

        if (! $grouped->isEmpty()) {
            $this->riderRiders = app(DeliveryRiderService::class)->listForStore(currentStoreId())
                ->filter(fn (DeliveryRider $rider) => $grouped->has($rider->id))
                ->map(function (DeliveryRider $rider) use ($grouped) {
                    return [
                        'id' => $rider->id,
                        'name' => $rider->name,
                        'phone' => $rider->phone,
                        'vehicle_label' => $rider->vehicle_label,
                        'is_active' => (bool) $rider->is_active,
                        'total' => (int) ($grouped->get($rider->id)?->total_count ?? 0),
                    ];
                })
                ->values()
                ->all();
        }

        // searchableRiders = ALL configured riders (not just those with
        // shipments) merged with their current filter totals — powers the
        // rider header-dropdown search list. @json stays single-line.
        $riderTotals = collect($this->riderRiders)->keyBy('id');
        $this->searchableRiders = app(DeliveryRiderService::class)->listForStore(currentStoreId())
            ->map(fn (DeliveryRider $r) => [
                'id' => $r->id,
                'name' => $r->name,
                'phone' => $r->phone,
                'vehicle_label' => $r->vehicle_label,
                'is_active' => (bool) $r->is_active,
                'total' => (int) ($riderTotals->get($r->id)['total'] ?? 0),
            ])
            ->values()
            ->all();
    }

    public function loadShipments(): void
    {
        $paginated = $this->baseTrackingQuery()
            ->paginate($this->perPage, ['*'], 'page', $this->page);

        $this->filteredTotal = $paginated->total();

        $items = collect($paginated->items());

        // Single query for the latest carrier note per tracking row (avoids N+1
        // while still resolving the notes column popup preview server-side).
        $latestNotes = \App\Models\Orders\OrderTrackingHistory::query()
            ->whereIn(
                'order_tracking_id',
                $items->map(fn (Order $o) => $o->latestTracking?->id)->filter()->values()->all(),
            )
            ->where('status', 'carrier_note')
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('order_tracking_id')
            ->mapWithKeys(fn ($rows, $trackingId) => [$trackingId => $rows->first()]);

        $this->shipments = $items
            ->map(function (Order $order) use ($latestNotes) {
                $tracking = $order->latestTracking;
                $latestNote = $tracking ? $latestNotes->get($tracking->id) : null;
                $isTrashed = (bool) $order->deleted_at;

                return [
                    'id' => $order->id,
                    'number' => $order->number,
                    'customer' => $order->customer?->name ?? '—',
                    'phone' => $order->customer?->phone ?? $order->phone ?? '—',
                    'total' => currency($order->total_amount),
                    'products' => $order->items
                        ->map(fn ($i) => [
                            'name' => trim((string) ($i->product?->name ?? '—')),
                            'qty' => max(1, (int) $i->quantity),
                        ])
                        ->filter(fn ($p) => $p['name'] !== '' && $p['name'] !== '—')
                        ->groupBy('name')
                        ->map(fn ($rows) => ['name' => $rows->first()['name'], 'qty' => (int) $rows->sum('qty')])
                        ->values()
                        ->all(),
                    'city' => $order->city?->name ?? '—',
                    'state' => $order->state?->name ?? '—',
                    'provider' => $order->shippingProvider?->name ?? '—',
                    'provider_logo' => $order->shippingProvider?->carrier?->logo,
                    'delivery_rider' => $order->deliveryRider?->name ?? '—',
                    'tracking_number' => $tracking?->tracking_number,
                    'tracking_id' => $tracking?->id,
                    'tracking_status' => $tracking?->tracking_status ?? null,
                    'shipped_at' => $tracking?->shipped_at,
                    'delivery_type' => $order->delivery_type,
                    'status_key' => $order->status?->key ?? null,
                    'status_color' => $order->status?->color ?? 'gray',
                    'confirmed_by' => $order->confirmedByHistory?->changedBy?->user?->name ?? null,
                    'assigned_to' => $order->assignedMembership?->user?->name ?? null,
                    'latest_note' => $latestNote?->notes ?? null,
                    'carrier_supports_api_notes' => (bool) ($order->shippingProvider?->carrier?->capabilityList()['api_notes'] ?? false),
                    'can_edit_order' => ! $isTrashed && ! in_array($order->status?->key, ['delivered', 'returned'], true),
                    'can_cancel_shipment' => ! $isTrashed && $this->shipmentCancelable($order, $tracking),
                    'is_trashed' => $isTrashed,
                ];
            })
            ->all();

        $this->trashCount = Order::where('store_id', currentStoreId())
            ->onlyTrashed()
            ->when($this->trackingTab === 'rider', fn ($q) => $q->whereNotNull('delivery_rider_id'))
            ->when($this->trackingTab === 'carrier', fn ($q) => $q->whereNotNull('shipping_provider_id'))
            ->count();

        $this->loadTrackingStats();
    }

    public function refresh(): void
    {
        $this->loadShipments();
    }

    public function nextPage(): void
    {
        $this->page++;
        $this->loadShipments();
    }

    public function previousPage(): void
    {
        if ($this->page > 1) {
            $this->page--;
            $this->loadShipments();
        }
    }

    public function shipmentCancelable(Order $order, ?OrderTracking $tracking): bool
    {
        if (! $tracking || ! in_array($order->status?->key, ['shipped', 'in_transit', 'out_for_delivery'], true)) {
            return false;
        }

        if ($tracking->isCarrierValidated()) {
            return false;
        }

        if ($tracking->tracking_number !== null) {
            return (bool) ($order->shippingProvider?->carrier?->capabilityList()['order_delete'] ?? false);
        }

        return $order->delivery_rider_id !== null;
    }
}
