<?php

namespace App\Domains\Analytics\Support;

use App\Domains\Analytics\DTOs\DashboardFilter;
use App\Domains\Shipping\Models\ShippingProvider;
use App\Enums\Store\StorePermissionEnum;
use App\Enums\Store\StoreRoleEnum;
use App\Models\Orders\Order;
use App\Models\Stores\Team\StoreMembership;
use Illuminate\Support\Collection;

final class DashboardFilterOptions
{
    public function carriers(string $storeId): Collection
    {
        $providerIds = Order::query()
            ->where('store_id', $storeId)
            ->whereNotNull('shipping_provider_id')
            ->pluck('shipping_provider_id')
            ->unique();

        return ShippingProvider::query()
            ->where('store_id', $storeId)
            ->whereIn('id', $providerIds)
            ->orderBy('name')
            ->get(['id', 'name', 'is_active']);
    }

    public function carrierSelectOptions(string $storeId): array
    {
        $options = [
            ['value' => '', 'label' => __('dashboard.all_carriers')],
        ];

        foreach ($this->carriers($storeId) as $carrier) {
            $options[] = [
                'value' => $carrier->id,
                'label' => $carrier->name,
                'hint' => $carrier->is_active ? null : __('dashboard.carrier_inactive'),
            ];
        }

        return $options;
    }

    /**
     * The members a stats tab may name: the store's active members, narrowed to
     * the tab's cohort (§ 11.4) when a dimension is given, so a tracking-only
     * member can never surface in the Confirmation view and vice-versa.
     */
    public function members(?StoreMembership $current, ?string $dimension = null, bool $includeTrashed = false): Collection
    {
        if (! $current || ! $current->is_active) {
            return collect();
        }

        $storeId = $current->store_id;

        // OWNER/ADMIN are roles, not permissions: StoreRoles grants both of them
        // TEAM_VIEW, so team visibility is fully described by these two checks.
        $hasTeamView = $current->hasPermission(StorePermissionEnum::STATS_TEAM_VIEW->value)
            || $current->hasPermission(StorePermissionEnum::TEAM_VIEW->value);

        $hasTeamViewOwn = $current->hasPermission(StorePermissionEnum::TEAM_VIEW_OWN->value)
            && ! $hasTeamView;

        // The confirmation team's without/with-trash toggle (D7): without trash
        // names the active members only; with trash also names soft-deleted
        // members, which the rows then mark as deleted.
        $query = StoreMembership::query()
            ->with('user')
            ->where('store_id', $storeId);

        $includeTrashed
            ? $query->withTrashed()
            : $query->where('is_active', true);

        app(DashboardMemberCohort::class)->constrain($query, $dimension);

        if ($hasTeamView) {
            // all
        } elseif ($hasTeamViewOwn) {
            $ids = $current->subordinates()->pluck('id')->merge([$current->id])->unique()->toArray();
            $query->whereIn('id', $ids);
        } else {
            $query->where('id', $current->id);
        }

        return $query->orderBy('id')->get()->map(function ($m) {
            return (object) [
                'id' => $m->id,
                'name' => $m->user?->name ?? $m->user?->email ?? $m->id,
                'role' => $m->role,
                'deleted' => method_exists($m, 'trashed') && $m->trashed(),
            ];
        });
    }

    public function memberSelectOptions(?StoreMembership $current, ?string $dimension = null): array
    {
        $options = [
            ['value' => '', 'label' => __('dashboard.all_members')],
        ];

        foreach ($this->members($current, $dimension) as $member) {
            $hint = null;
            if (! empty($member->role)) {
                try {
                    $hint = StoreRoleEnum::from($member->role)->label();
                } catch (\Throwable) {
                    $hint = null;
                }
            }

            $options[] = [
                'value' => $member->id,
                'label' => $member->name,
                'hint' => $hint,
            ];
        }

        // The sentinel cohort is always a store-wide option, but its label
        // follows the active key (PHASE 37-M, D1): on Confirmation the key is
        // the assignment, so it reads «غير مُسنَدة» (no work); on Delivery the
        // key is the financial credit, so it reads «بلا رصيد» (no credit).
        $options[] = [
            'value' => DashboardFilter::UNATTRIBUTED,
            'label' => $dimension === 'delivery'
                ? __('dashboard.team_unattributed')
                : __('dashboard.team_unassigned'),
        ];

        return $options;
    }
}
