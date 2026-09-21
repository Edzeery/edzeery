<?php

namespace App\Models\Orders\Concerns;

use App\Enums\Store\StorePermissionEnum;
use App\Models\Stores\Team\StoreMembership;
use App\Services\Stores\StoreProductScopeService;
use Illuminate\Database\Eloquent\Builder;

/**
 * Membership-based visibility scoping for Order queries (Phase 36.4).
 *
 * Explicitly-invoked (never global): consumers chain ->visibleTo($membership)
 * on their own query. Unrestricted memberships (TEAM_VIEW — owner/admin) keep
 * the query untouched; TEAM_VIEW_OWN memberships (managers) see their own
 * orders plus their supervised staff's, optionally narrowed to the products
 * assigned to the manager via StoreProductScopeService; everyone else (staff)
 * sees only orders assigned to their own membership.
 *
 * The membership is nullable on purpose: canStore() short-circuits to true
 * for platform super_admin/admin, which can precede membership resolution, so
 * a null membership is a no-op scope rather than a TypeError.
 */
trait HasVisibilityScope
{
    public function scopeVisibleTo(Builder $query, ?StoreMembership $membership): Builder
    {
        if ($membership === null) {
            return $query;
        }

        if ($membership->can(StorePermissionEnum::TEAM_VIEW)) {
            return $query;
        }

        if ($membership->can(StorePermissionEnum::TEAM_VIEW_OWN)) {
            $teamIds = $membership->subordinates()->pluck('id')->push($membership->id);

            $query->whereIn('assigned_to_membership_id', $teamIds);

            $productIds = app(StoreProductScopeService::class)
                ->assignedProductIds($membership->store, $membership);

            if (! empty($productIds)) {
                $query->whereHas('items', fn ($q) => $q->whereIn('product_id', $productIds));
            }

            return $query;
        }

        return $query->where('assigned_to_membership_id', $membership->id);
    }
}