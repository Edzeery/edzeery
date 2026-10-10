<?php

namespace App\Domains\Analytics\Support;

use App\Domains\Analytics\DTOs\DashboardFilter;
use App\Domains\Shipping\Models\ShippingProvider;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use Carbon\CarbonImmutable;

final class DashboardFilterFactory
{
    private const FALLBACK_TIMEZONE = 'Africa/Algiers';

    /**
     * @param  array<string, mixed>  $input
     */
    public function make(array $input, ?StoreMembership $currentMembership): DashboardFilter
    {
        $storeId = $currentMembership?->store_id ?? currentStoreId();
        $timezone = $this->resolveTimezone($storeId);
        $now = CarbonImmutable::now($timezone);

        $period = $this->resolvePeriodName($input);

        // Boundaries are built in the store's timezone (so "today" starts at
        // local midnight) and handed over as UTC, because orders.created_at is
        // stored in UTC and every query compares against that clock.
        [$from, $to] = $this->resolveWindow($period, $now, $input);

        [$memberId, $memberDimension, $memberScopeIds, $memberLocked, $memberUnattributedOnly] = $this->resolveMember(
            $currentMembership,
            $input['memberId'] ?? null,
            $input['memberDimension'] ?? null
        );

        return new DashboardFilter(
            period: $period,
            from: $from?->utc(),
            to: $to?->utc(),
            timezone: $timezone,
            // Buckets are shifted by the offset in effect at the end of the window.
            utcOffsetSeconds: ($to ?? $now)->getOffset(),
            carrierId: $this->resolveCarrier($input, $storeId),
            memberId: $memberId,
            memberDimension: $memberDimension,
            memberScopeIds: $memberScopeIds,
            storeId: $storeId ?? '',
            memberLocked: $memberLocked,
            memberUnattributedOnly: $memberUnattributedOnly,
        );
    }

    /**
     * @param  array<string, mixed>  $input
     */
    private function resolvePeriodName(array $input): string
    {
        $period = strtolower((string) ($input['period'] ?? 'today'));

        return in_array($period, ['all', 'today', 'yesterday', 'week', 'month', 'custom'], true)
            ? $period
            : 'today';
    }

    private function resolveTimezone(?string $storeId): string
    {
        // The store's timezone lives in store_settings (store_settings.timezone).
        $store = currentStore() ?? ($storeId ? Store::query()->find($storeId) : null);

        $timezone = $store?->settings?->timezone;

        if (! is_string($timezone) || ! in_array($timezone, timezone_identifiers_list(), true)) {
            return self::FALLBACK_TIMEZONE;
        }

        return $timezone;
    }

    private function resolveCarrier(array $input, ?string $storeId): ?string
    {
        $carrierId = $input['carrierId'] ?? null;

        if (! $carrierId || ! $storeId) {
            return null;
        }

        $exists = ShippingProvider::where('store_id', $storeId)
            ->where('id', $carrierId)
            ->exists();

        return $exists ? (string) $carrierId : null;
    }

    /**
     * @return array{0: ?CarbonImmutable, 1: ?CarbonImmutable}
     */
    private function resolveWindow(string $period, CarbonImmutable $now, array $input): array
    {
        return match ($period) {
            'all' => [null, null],
            'today' => [$now->startOfDay(), $now->endOfDay()],
            'yesterday' => [$now->subDay()->startOfDay(), $now->subDay()->endOfDay()],
            'week' => [$now->subDays(6)->startOfDay(), $now->endOfDay()],
            'month' => [$now->startOfMonth()->startOfDay(), $now->endOfDay()],
            'custom' => $this->resolveCustom($now, $input),
            default => [$now->startOfDay(), $now->endOfDay()],
        };
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array{0: ?CarbonImmutable, 1: ?CarbonImmutable}
     */
    private function resolveCustom(CarbonImmutable $now, array $input): array
    {
        // An incomplete or unreadable range falls back to today; the view warns
        // the user that the range it asked for was adjusted.
        $today = [$now->startOfDay(), $now->endOfDay()];

        $fromInput = $input['dateFrom'] ?? null;
        $toInput = $input['dateTo'] ?? null;

        if (! $fromInput || ! $toInput) {
            return $today;
        }

        try {
            $from = CarbonImmutable::parse($fromInput, $now->timezone)->startOfDay();
            $to = CarbonImmutable::parse($toInput, $now->timezone)->endOfDay();
        } catch (\Throwable) {
            return $today;
        }

        if ($from->greaterThan($to)) {
            [$from, $to] = [$to->startOfDay(), $from->endOfDay()];
        }

        return $from->diffInDays($to) > 366
            ? [$from, $from->addDays(366)->endOfDay()]
            : [$from, $to];
    }

    /**
     * Returns [memberId, dimension, memberScopeIds, memberLocked,
     * memberUnattributedOnly], where memberScopeIds is null for "every member"
     * and an explicit list otherwise. The sentinel UNATTRIBUTED selects the
     * unattributed cohort (confirmed_by IS NULL).
     *
     * @return array{0: ?string, 1: string, 2: ?array<int, string>, 3: bool, 4: bool}
     */
    private function resolveMember(?StoreMembership $current, ?string $memberId, ?string $memberDimension): array
    {
        if (! $current || ! $current->is_active) {
            // Fail closed: without a membership nothing is visible.
            return [null, 'confirmation', [], true, false];
        }

        // OWNER/ADMIN are roles, not permissions: StoreRoles grants both of them
        // TEAM_VIEW, so team visibility is fully described by these two checks.
        $hasTeamView = $current->hasPermission(StorePermissionEnum::STATS_TEAM_VIEW->value)
            || $current->hasPermission(StorePermissionEnum::TEAM_VIEW->value);

        $hasTeamViewOwn = ! $hasTeamView
            && $current->hasPermission(StorePermissionEnum::TEAM_VIEW_OWN->value);

        $canConfirm = $current->hasPermission(StorePermissionEnum::STATS_CONFIRMATION->value)
            || $current->hasPermission(StorePermissionEnum::ORDER_CONFIRM->value);

        $canDeliver = $current->hasPermission(StorePermissionEnum::STATS_DELIVERY->value);

        $dimension = $this->resolveDimension($memberDimension, $canConfirm, $canDeliver);

        // The unattributed cohort (confirmed_by IS NULL) is a store-wide pick,
        // exposed when the member can see the whole team's numbers. The sentinel
        // is not a membership id, so it can never reach keepIfAllowed.
        if ($hasTeamView && $memberId === DashboardFilter::UNATTRIBUTED) {
            return [DashboardFilter::UNATTRIBUTED, $dimension, [DashboardFilter::UNATTRIBUTED], false, true];
        }

        if ($hasTeamView) {
            $allowed = StoreMembership::query()
                ->where('store_id', $current->store_id)
                ->where('is_active', true)
                ->pluck('id')
                ->all();

            $selected = $this->keepIfAllowed($memberId, $allowed);

            // No pick means no restriction: this is the whole point of a free choice.
            return [$selected, $dimension, $selected ? [$selected] : null, false, false];
        }

        if ($hasTeamViewOwn) {
            $allowed = $current->subordinates()
                ->where('is_active', true)
                ->pluck('id')
                ->push($current->id)
                ->unique()
                ->all();

            $selected = $this->keepIfAllowed($memberId, $allowed);

            // No pick means the whole team: self plus subordinates.
            return [$selected, $dimension, $selected ? [$selected] : $allowed, false, false];
        }

        return [$current->id, $dimension, [$current->id], true, false];
    }

    /**
     * @param  array<int, string>  $allowed
     */
    private function keepIfAllowed(?string $memberId, array $allowed): ?string
    {
        return $memberId !== null && in_array($memberId, $allowed, true) ? $memberId : null;
    }

    /**
     * A dimension is only honoured when the member actually holds that
     * capability; otherwise they fall back to confirmation when they can
     * confirm, and to delivery when they cannot.
     */
    private function resolveDimension(?string $requested, bool $canConfirm, bool $canDeliver): string
    {
        $requested = strtolower($requested ?? '');

        if ($requested === 'confirmation' && $canConfirm) {
            return 'confirmation';
        }

        if ($requested === 'delivery' && $canDeliver) {
            return 'delivery';
        }

        return $canConfirm ? 'confirmation' : 'delivery';
    }
}
