<?php

namespace App\Services\Stores;

use App\Domains\Order\Models\ConfirmationProductAssignment;
use App\Models\Products\Product;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;

class StoreProductScopeService
{
    /**
     * Grant a manager's own membership the given product (same store, upsert).
     * This is the Phase 36.3 management path — it intentionally writes through
     * a separate code path from OrderAssignmentService's auto-assign tiering,
     * which reads the same table untouched.
     */
    public function assign(Store $store, StoreMembership $manager, Product $product): void
    {
        $this->assertScopeable($store, $manager, $product);

        ConfirmationProductAssignment::query()->updateOrCreate(
            [
                'store_id' => $store->id,
                'membership_id' => $manager->id,
                'product_id' => $product->id,
            ],
            []
        );
    }

    /**
     * Revoke a single assignment row for the given product (same store).
     * Only that exact row is deleted — nothing else is touched.
     */
    public function revoke(Store $store, StoreMembership $manager, Product $product): void
    {
        $this->assertScopeable($store, $manager, $product);

        ConfirmationProductAssignment::query()
            ->where('store_id', $store->id)
            ->where('membership_id', $manager->id)
            ->where('product_id', $product->id)
            ->delete();
    }

    /**
     * Product ids currently assigned to this manager's membership (same store).
     */
    public function assignedProductIds(Store $store, StoreMembership $manager): array
    {
        return ConfirmationProductAssignment::query()
            ->where('store_id', $store->id)
            ->where('membership_id', $manager->id)
            ->pluck('product_id')
            ->all();
    }

    /**
     * Only manager-role memberships of the same store can carry a product
     * scope; the product itself must also belong to the store.
     */
    private function assertScopeable(Store $store, StoreMembership $manager, ?Product $product = null): void
    {
        if (! $manager->isManager()) {
            throw new \InvalidArgumentException(__('teams.product_scope_role_only'));
        }

        if ($manager->store_id !== $store->id || ($product && $product->store_id !== $store->id)) {
            throw new \InvalidArgumentException(__('teams.product_scope_cross_store'));
        }
    }
}