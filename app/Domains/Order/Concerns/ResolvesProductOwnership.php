<?php

namespace App\Domains\Order\Concerns;

use Illuminate\Support\Facades\DB;

/**
 * Product-ownership map feeding both auto-assignment pipelines: how many of an
 * order's distinct products each member owns via confirmation_product_assignments.
 * Owners form the ownership pool the pipelines try before the general pool, and
 * the count is the coverage ranking key — the denominator (distinct ordered
 * products) is identical for every member of one order, so comparing counts is
 * exactly comparing coverage. One grouped query per assignment call; an empty
 * product set yields no pool (every member stays a general candidate).
 */
trait ResolvesProductOwnership
{
    protected function ownershipCounts(string $storeId, array $productIds): array
    {
        if (empty($productIds)) {
            return [];
        }

        return DB::table('confirmation_product_assignments')
            ->where('store_id', $storeId)
            ->whereIn('product_id', $productIds)
            ->groupBy('membership_id')
            ->selectRaw('membership_id, COUNT(DISTINCT product_id) as owned_products')
            ->get()
            ->pluck('owned_products', 'membership_id')
            ->map(fn ($count) => (int) $count)
            ->all();
    }
}
