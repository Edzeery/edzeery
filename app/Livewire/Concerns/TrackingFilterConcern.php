<?php

namespace App\Livewire\Concerns;

use App\Domains\Shipping\Services\DeliveryRiderService;

/**
 * Tracking-grid filters, active-filter accounting and header/drill-down filter
 * actions for the merchant tracking page — extracted out of TrackingGridConcern
 * so each concern stays within its line budget. Filters are component state
 * ($this->filters / $this->allMembers / $this->visibleColumns), so these methods
 * are trait-independent of the grid query builder.
 */
trait TrackingFilterConcern
{
    public function clearFilters(): void
    {
        $this->search = '';
        $this->filters = [
            'provider' => null,
            'tracking_statuses' => [],
            'date_from' => null,
            'date_to' => null,
            'amount_min' => null,
            'amount_max' => null,
            'city' => null,
            'state' => null,
            'products' => [],
            'rider' => null,
            'assigned_to' => null,
            'confirmed_by' => null,
            'can_open' => null,
            'send_from_carrier_warehouse' => null,
            'shipment_type' => null,
        ];
        $this->page = 1;
        $this->loadShipments();
    }

    // Filter groups available to the toolbar "Filters" drill-down portal. Every
    // filter is always offered there regardless of column visibility (visible
    // columns additionally expose the same filter via their header icon), so the
    // trigger is never empty. Only tab-specific groups are constrained.
    public function availableFilterGroups(): array
    {
        $map = [
            'state' => null,
            'provider' => 'carrier',
            'tracking_statuses' => null,
            'amount' => null,
            'products' => null,
            'city' => null,
            'rider' => 'rider',
            'assigned_to' => null,
            'confirmed_by' => null,
            'can_open' => null,
            'send_from_carrier_warehouse' => null,
            'shipment_type' => null,
        ];

        $available = [];

        foreach ($map as $group => $tab) {
            if ($tab !== null && $this->trackingTab !== $tab) {
                continue;
            }

            $available[] = $group;
        }

        return $available;
    }

    // Counts active filters among the *available* drill-down groups plus the
    // always-available toolbar date range — the badge on the Filters trigger +
    // date button mirrors what the UI can clear.
    public function activeFilterCount(): int
    {
        $count = 0;

        foreach ($this->availableFilterGroups() as $group) {
            switch ($group) {
                case 'tracking_statuses':
                    if (count($this->filters['tracking_statuses'] ?? []) > 0) {
                        $count++;
                    }
                    break;
                case 'amount':
                    if (filled($this->filters['amount_min'] ?? null) || filled($this->filters['amount_max'] ?? null)) {
                        $count++;
                    }
                    break;
                case 'products':
                    if (count($this->filters['products'] ?? []) > 0) {
                        $count++;
                    }
                    break;
                case 'can_open':
                case 'send_from_carrier_warehouse':
                    if (($this->filters[$group] ?? null) !== null) {
                        $count++;
                    }
                    break;
                default:
                    if (filled($this->filters[$group] ?? null)) {
                        $count++;
                    }
            }
        }

        // Date range lives directly in the toolbar (its own calendar trigger).
        if (filled($this->filters['date_from'] ?? null) || filled($this->filters['date_to'] ?? null)) {
            $count++;
        }

        return $count;
    }

    public function setFilter(string $key, $value): void
    {
        if ($key === 'rider' && $value !== null) {
            $rider = app(DeliveryRiderService::class)->findForStore($value, currentStoreId());

            if (! $rider) {
                return;
            }
        }

        if (in_array($key, ['assigned_to', 'confirmed_by'], true) && $value !== null) {
            $member = collect($this->allMembers)->firstWhere('id', $value);

            if (! $member) {
                return;
            }
        }

        if (in_array($key, ['can_open', 'send_from_carrier_warehouse'], true) && $value !== null && ! is_bool($value)) {
            $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }

        if ($key === 'shipment_type' && $value !== null && ! in_array($value, ['delivery', 'exchange', 'pickup'], true)) {
            return;
        }

        $this->filters[$key] = $value;
        $this->page = 1;
        $this->loadShipments();
    }

    public function toggleTrackingStatus(string $value): void
    {
        $current = $this->filters['tracking_statuses'] ?? [];
        $this->filters['tracking_statuses'] = in_array($value, $current, true)
            ? array_values(array_diff($current, [$value]))
            : array_merge($current, [$value]);
        $this->page = 1;
        $this->loadShipments();
    }

    public function toggleProductFilter(string $value): void
    {
        $current = $this->filters['products'] ?? [];
        $this->filters['products'] = in_array($value, $current, true)
            ? array_values(array_diff($current, [$value]))
            : array_merge($current, [$value]);
        $this->page = 1;
        $this->loadShipments();
    }

    // Date range lives in the toolbar as its own trigger — one round-trip reset.
    public function clearDateFilter(): void
    {
        $this->filters['date_from'] = null;
        $this->filters['date_to'] = null;
        $this->page = 1;
        $this->loadShipments();
    }
}