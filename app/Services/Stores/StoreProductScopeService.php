<?php

namespace App\Services\Stores;

use App\Enums\Store\StorePermissionEnum;
use App\Models\Products\Product;
use App\Models\Stores\Store;
use App\Models\Stores\Team\MembershipProductScope;
use App\Models\Stores\Team\StoreMembership;
use Illuminate\Support\Collection;

class StoreProductScopeService
{
    /**
     * Grant a manager's own membership the given product (same store, upsert).
     *
     * Writes to membership_product_scopes — the visibility-scope table — which
     * is intentionally NOT confirmation_product_assignments: that table stays
     * the read-only source for OrderAssignmentService's specialist tiering.
     */
    public function assign(Store $store, StoreMembership $manager, Product $product): void
    {
        $this->assertScopeable($store, $manager, $product);

        MembershipProductScope::query()->updateOrCreate(
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

        MembershipProductScope::query()
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
        return $this->scopeProductIds($store->id, $manager->id);
    }

    /**
     * Would an order carrying these products be visible to this membership?
     *
     * Mirrors HasVisibilityScope::scopeVisibleTo() exactly: only TEAM_VIEW_OWN
     * (manager) memberships are narrowed by product, and the match is ANY-match
     * (the order must contain at least one scoped product), never all-match.
     * This is the guard that stops assignment from creating an order the
     * assignee cannot open.
     */
    public function canSeeAnyProduct(StoreMembership $membership, array $productIds): bool
    {
        if (empty($productIds)) {
            return true;
        }

        return ! $this->isUnscoped($membership)
            && $this->scopeAllows(
                $this->scopeProductIds($membership->store_id, $membership->id),
                $productIds,
            );
    }

    /**
     * Batch form of canSeeAnyProduct() for candidate pools: one scope query for
     * the whole set regardless of member count (no query per candidate), then
     * drop only the members that are product-scoped away from every product.
     */
    public function filterByVisibility(Collection $memberships, array $productIds): Collection
    {
        $memberships = $memberships->values();

        if ($memberships->isEmpty() || empty($productIds)) {
            return $memberships;
        }

        $scopedIds = $memberships
            ->filter(fn (StoreMembership $m) => ! $this->isUnscoped($m))
            ->pluck('id')
            ->all();

        if ($scopedIds === []) {
            return $memberships;
        }

        $scopeByMembership = $this->scopeMap($memberships->first()->store_id, $scopedIds);

        return $memberships->reject(function (StoreMembership $m) use ($scopeByMembership, $productIds) {
            return ! $this->scopeAllows($scopeByMembership[$m->id] ?? [], $productIds);
        })->values();
    }

    /**
     * Product ids of one membership, keyed by nothing — single-membership read.
     */
    private function scopeProductIds(string $storeId, string $membershipId): array
    {
        return MembershipProductScope::query()
            ->where('store_id', $storeId)
            ->where('membership_id', $membershipId)
            ->pluck('product_id')
            ->all();
    }

    /**
     * Scoped product ids for many memberships in one query, keyed by membership.
     */
    private function scopeMap(string $storeId, array $membershipIds): array
    {
        if ($membershipIds === []) {
            return [];
        }

        $map = [];

        foreach (
            MembershipProductScope::query()
                ->where('store_id', $storeId)
                ->whereIn('membership_id', $membershipIds)
                ->get(['membership_id', 'product_id']) as $scope
        ) {
            $map[$scope->membership_id][] = $scope->product_id;
        }

        return $map;
    }

    /**
     * Memberships whose visibility is not narrowed by product at all: anyone
     * without TEAM_VIEW_OWN — owner/admin see everything, staff are confined by
     * assignment only, neither consults the scope table.
     */
    private function isUnscoped(StoreMembership $membership): bool
    {
        return ! $membership->can(StorePermissionEnum::TEAM_VIEW_OWN);
    }

    /**
     * No scope rows means unrestricted; otherwise at least one scoped product
     * must be present (any-match, same rule as the visibleTo query).
     */
    private function scopeAllows(array $scope, array $productIds): bool
    {
        return $scope === [] || $this->scopeIntersects($scope, $productIds);
    }

    private function scopeIntersects(array $scope, array $productIds): bool
    {
        return array_intersect($scope, $productIds) !== [];
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