<?php

namespace App\Domains\Analytics\Support;

use App\Domains\Analytics\DTOs\DashboardFilter;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Stores\Shipping\StoreShippingProvider;
use App\Models\Stores\Team\StoreMembership;
use Carbon\CarbonImmutable;

final class DashboardFilterFactory
{
    public function make(array $input, ?StoreMembership $currentMembership): DashboardFilter
    {
        $storeId = $currentMembership?->store_id ?? currentStoreId();
        $tz = config('app.timezone') ?? 'UTC';
        $now = CarbonImmutable::now($tz);

        $period = strtolower($input['period'] ?? 'today');
        $allowedPeriods = ['all', 'today', 'yesterday', 'week', 'month', 'custom'];
        if (! in_array($period, $allowedPeriods, true)) {
            $period = 'today';
        }

        [$from, $to, $prevFrom, $prevTo] = $this->resolvePeriod($period, $now, $input);

        $carrierId = $input['carrierId'] ?? null;
        if ($carrierId) {
            $exists = StoreShippingProvider::where('store_id', $storeId)
                ->where('id', $carrierId)
                ->exists();
            if (! $exists) {
                $carrierId = null;
            }
        }

        [$memberId, $memberDimension, $allowedMembershipIds] = $this->resolveMember(
            $currentMembership,
            $input['memberId'] ?? null,
            $input['memberDimension'] ?? null
        );

        return new DashboardFilter(
            period: $period,
            from: $from,
            to: $to,
            previousFrom: $prevFrom,
            previousTo: $prevTo,
            carrierId: $carrierId,
            memberId: $memberId,
            memberDimension: $memberDimension,
            allowedMembershipIds: $allowedMembershipIds,
            storeId: $storeId ?? '',
        );
    }

    private function resolvePeriod(string $period, CarbonImmutable $now, array $input): array
    {
        $tz = $now->timezone;

        return match ($period) {
            'all' => [null, null, null, null],
            'today' => [
                $now->startOfDay(),
                $now->endOfDay(),
                $now->subDay()->startOfDay(),
                $now->subDay()->endOfDay(),
            ],
            'yesterday' => [
                $now->subDay()->startOfDay(),
                $now->subDay()->endOfDay(),
                $now->subDays(2)->startOfDay(),
                $now->subDays(2)->endOfDay(),
            ],
            'week' => [
                $now->subDays(6)->startOfDay(),
                $now->endOfDay(),
                $now->subDays(13)->startOfDay(),
                $now->subDays(7)->endOfDay(),
            ],
            'month' => [
                $now->startOfMonth()->startOfDay(),
                $now->endOfDay(),
                $now->subMonth()->startOfMonth()->startOfDay(),
                $now->subMonth()->endOfMonth()->endOfDay(),
            ],
            'custom' => $this->resolveCustom($now, $input),
            default => [
                $now->startOfDay(),
                $now->endOfDay(),
                $now->subDay()->startOfDay(),
                $now->subDay()->endOfDay(),
            ],
        };
    }

    private function resolveCustom(CarbonImmutable $now, array $input): array
    {
        $tz = $now->timezone;
        $fromStr = $input['dateFrom'] ?? null;
        $toStr = $input['dateTo'] ?? null;
        if (! $fromStr || ! $toStr) {
            return [
                $now->startOfDay(),
                $now->endOfDay(),
                $now->subDay()->startOfDay(),
                $now->subDay()->endOfDay(),
            ];
        }

        try {
            $from = CarbonImmutable::parse($fromStr, $tz)->startOfDay();
            $to = CarbonImmutable::parse($toStr, $tz)->endOfDay();
        } catch (\Throwable) {
            return [
                $now->startOfDay(),
                $now->endOfDay(),
                $now->subDay()->startOfDay(),
                $now->subDay()->endOfDay(),
            ];
        }

        if ($from->greaterThan($to)) {
            [$from, $to] = [$to->startOfDay(), $from->endOfDay()];
        }

        $diff = $from->diffInDays($to);
        if ($diff > 366) {
            $to = $from->addDays(366)->endOfDay();
        }

        $len = $from->diffInSeconds($to) + 1;
        $prevTo = $from->subSecond();
        $prevFrom = $prevTo->subSeconds($len - 1)->startOfDay();

        return [$from, $to, $prevFrom, $prevTo];
    }

    private function resolveMember(?StoreMembership $current, ?string $memberId, ?string $memberDimension): array
    {
        if (! $current || ! $current->is_active) {
            return [null, null, null];
        }

        $storeId = $current->store_id;

        $hasTeamView = $current->hasPermission(StorePermissionEnum::STATS_TEAM_VIEW->value)
            || $current->hasPermission(StorePermissionEnum::TEAM_VIEW->value)
            || $current->hasAnyPermission([StorePermissionEnum::OWNER->value, StorePermissionEnum::ADMIN->value]);

        $hasTeamViewOwn = $current->hasPermission(StorePermissionEnum::TEAM_VIEW_OWN->value)
            && ! $hasTeamView;

        $hasStatsConfirm = $current->hasPermission(StorePermissionEnum::STATS_CONFIRMATION->value)
            || $current->hasPermission(StorePermissionEnum::ORDER_CONFIRM->value);

        $defaultDim = $hasStatsConfirm ? 'confirmation' : 'delivery';

        $dim = strtolower($memberDimension ?? '');
        if (! in_array($dim, ['confirmation', 'delivery'], true)) {
            $dim = $defaultDim;
        }

        if ($hasTeamView) {
            $allowed = StoreMembership::query()
                ->where('store_id', $storeId)
                ->where('is_active', true)
                ->pluck('id')
                ->toArray();

            $mid = $memberId;
            if ($mid && ! in_array($mid, $allowed, true)) {
                $mid = null;
            }

            return [$mid, $dim, $allowed];
        }

        if ($hasTeamViewOwn) {
            $subs = $current->subordinates();
            $allowed = $subs->merge([$current])->pluck('id')->unique()->toArray();
            $mid = $memberId;
            if ($mid && ! in_array($mid, $allowed, true)) {
                $mid = null;
            }
            if (! $mid) {
                $mid = $current->id;
            }

            return [$mid, $dim, $allowed];
        }

        return [$current->id, $defaultDim, [$current->id]];
    }
}
