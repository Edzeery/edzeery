<?php

namespace App\Domains\Shipping\Services;

use App\Domains\Shipping\Models\DeliveryRider;

class DeliveryRiderService
{
    /**
     * List the active riders for a given store, ordered by name.
     */
    private static array $listCache = [];

    public function listForStore(?string $storeId = null, bool $onlyActive = false)
    {
        $key = ($storeId ?? currentStoreId()).'|'.($onlyActive ? '1' : '0');

        if (isset(self::$listCache[$key])) {
            return self::$listCache[$key];
        }

        $query = DeliveryRider::query()->forStore($storeId);

        if ($onlyActive) {
            $query->active();
        }

        return self::$listCache[$key] = $query->withCount('orders')->orderBy('name')->get();
    }

    /**
     * Find a single rider belonging to a store.
     */
    public function findForStore(string $id, ?string $storeId = null): ?DeliveryRider
    {
        return DeliveryRider::query()
            ->forStore($storeId)
            ->withCount('orders')
            ->find($id);
    }

    /**
     * Create a new rider for a store.
     */
    public function create(?string $storeId, array $data): DeliveryRider
    {
        return DeliveryRider::create(array_merge($data, ['store_id' => $storeId]));
    }

    /**
     * Update an existing rider (scoped to the store).
     */
    public function update(string $id, ?string $storeId, array $data): ?DeliveryRider
    {
        $rider = $this->findForStore($id, $storeId);

        if (! $rider) {
            return null;
        }

        $rider->update($data);

        return $rider;
    }

    /**
     * Delete a rider (scoped to the store).
     */
    public function delete(string $id, ?string $storeId): bool
    {
        $rider = $this->findForStore($id, $storeId);

        if (! $rider) {
            return false;
        }

        return (bool) $rider->delete();
    }
}
