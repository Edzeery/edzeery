<?php

namespace App\Domains\Order\Support;

use App\Models\Stores\Team\StoreMembership;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| PHASE 35.3 — strict product-ownership routing
|--------------------------------------------------------------------------
|
| Decides which candidate pool one order (or tracking) may draw from, for one
| role scope, following the distribution rules:
|
|  R1  A product is OWNED when at least one role-scoped assignment row belongs
|      to an active store membership that currently holds the role permission.
|      Shift, cap and visibility are irrelevant to ownership itself.
|  R2  If the order owns any product, the pool is exactly the eligible
|      candidates assigned to at least one of the order's owned products.
|      Unowned products ride along, and there is NO fallback outside the pool:
|      if nobody in it is eligible the order waits (capacity alert), even for
|      overflow or handover.
|  R3  If the order owns no product, the pool is the eligible candidates with
|      ZERO role assignment rows in the store. When nobody is such a "general"
|      member (everyone owns something) the pool widens to every eligible
|      candidate instead.
|
| The router only shapes the candidate set and the coverage ranking key; caps,
| shifts, overflow and the actual pick stay in ResolvesCapacityBalancedCandidates.
| All reads are batched: one query per call, independent of owner/candidate count.
|
*/

class ProductOwnershipRouter
{
    /**
     * @param  string  $roleScope  'confirm' | 'track'
     * @param  Collection<int, StoreMembership>  $roleMembers  Active members holding the role permission (ownership authority).
     * @param  Collection<int, StoreMembership>  $candidates  Visibility-filtered subset that may actually be assigned.
     * @param  array<int, string>  $productIds  Distinct order product ids (empty = no product dimension).
     * @return array{mode: string, pool: Collection<int, StoreMembership>, coverage: array<string, int>, owned_products: array<int, string>}
     */
    public function route(
        string $storeId,
        string $roleScope,
        Collection $roleMembers,
        Collection $candidates,
        array $productIds,
    ): array {
        if ($candidates->isEmpty()) {
            return ['mode' => 'empty', 'pool' => collect(), 'coverage' => [], 'owned_products' => []];
        }

        $roleMemberIdSet = array_flip($roleMembers->pluck('id')->all());
        $productIdSet = array_flip($productIds);

        $assignmentMemberIds = [];
        $coverage = [];
        $ownedProducts = [];

        foreach ($this->roleRows($storeId, $roleScope) as $row) {
            $membershipId = $row->membership_id;

            // R1: only active permission holders can confer ownership.
            if (! isset($roleMemberIdSet[$membershipId])) {
                continue;
            }

            $assignmentMemberIds[$membershipId] = true;

            if (isset($productIdSet[$row->product_id])) {
                $ownedProducts[$row->product_id] = true;
                $coverage[$membershipId] = ($coverage[$membershipId] ?? 0) + 1;
            }
        }

        if ($ownedProducts !== []) {
            // R2: strict ownership pool, no fallback.
            $ownerIdSet = array_flip(array_keys($coverage));

            return [
                'mode' => 'owner',
                'pool' => $this->intersect($candidates, $ownerIdSet),
                'coverage' => $coverage,
                'owned_products' => array_keys($ownedProducts),
            ];
        }

        // R3: no owned product → general members (zero role rows anywhere).
        $generalIdSet = array_flip(array_diff(
            array_keys($roleMemberIdSet),
            array_keys($assignmentMemberIds),
        ));

        if ($generalIdSet === []) {
            // Everyone owns something: widen to every eligible candidate.
            return [
                'mode' => 'all',
                'pool' => $candidates->values(),
                'coverage' => [],
                'owned_products' => [],
            ];
        }

        return [
            'mode' => 'general',
            'pool' => $this->intersect($candidates, $generalIdSet),
            'coverage' => [],
            'owned_products' => [],
        ];
    }

    /**
     * Every role-scoped assignment row of the store (membership + product), read
     * once. Ownership AND the general-member test both derive from this batch.
     *
     * @return Collection<int, object>
     */
    private function roleRows(string $storeId, string $roleScope): Collection
    {
        return DB::table('confirmation_product_assignments')
            ->where('store_id', $storeId)
            ->where('role_scope', $roleScope)
            ->get(['membership_id', 'product_id']);
    }

    /**
     * Keep candidates whose membership id is in the allowed set.
     *
     * @param  array<string, int>  $allowedIdSet
     * @return Collection<int, StoreMembership>
     */
    private function intersect(Collection $candidates, array $allowedIdSet): Collection
    {
        return $candidates
            ->filter(fn (StoreMembership $member) => isset($allowedIdSet[$member->id]))
            ->values();
    }
}
