<?php

namespace App\Livewire\Concerns;

use App\Models\Orders\Order;

/**
 * Multi-select + bulk-action state for the merchant tracking page. Selection is
 * store-scoped and trimmed to non-trashed orders on every load, so ids of orders
 * deleted elsewhere (or filtered away) never leak into the next bulk action.
 */
trait TrackingBulkSelectionConcern
{
    public function toggleSelectOrder(string $id): void
    {
        if ($this->showTrash) {
            return;
        }

        if (! Order::where('id', $id)->where('store_id', currentStoreId())->whereNull('deleted_at')->exists()) {
            return;
        }

        $this->selectedShipments = in_array($id, $this->selectedShipments, true)
            ? array_values(array_diff($this->selectedShipments, [$id]))
            : array_values(array_merge($this->selectedShipments, [$id]));

        $this->syncSelectState();
    }

    public function toggleSelectAll(bool $value): void
    {
        if ($this->showTrash) {
            $this->clearSelection();

            return;
        }

        if (! $value) {
            $this->clearSelection();

            return;
        }

        $this->selectedShipments = $this->baseTrackingQuery(true)
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->values()
            ->all();

        $this->syncSelectState();
    }

    public function clearSelection(): void
    {
        $this->selectedShipments = [];
        $this->selectModeActive = false;
        $this->selectAllChecked = false;
    }

    public function syncBulkSelection(): void
    {
        if (empty($this->selectedShipments)) {
            $this->syncSelectState();

            return;
        }

        $valid = Order::where('store_id', currentStoreId())
            ->whereIn('id', $this->selectedShipments)
            ->whereNull('deleted_at')
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->values()
            ->all();

        $this->selectedShipments = $valid;
        $this->syncSelectState();
    }

    protected function syncSelectState(): void
    {
        $total = (int) ($this->filteredTotal ?? 0);
        $count = count($this->selectedShipments);

        $this->selectModeActive = $count > 0;
        $this->selectAllChecked = $count > 0 && $count === $total;
    }
}