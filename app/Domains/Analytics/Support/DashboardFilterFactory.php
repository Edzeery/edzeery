<?php

namespace App\Domains\Analytics\Support;

use App\Domains\Analytics\DTOs\DashboardFilter;
use App\Domains\Shipping\Models\ShippingProvider;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use Carbon\CarbonImmutable;

final class DashboardFilterFactory
{
    private const FALLBACK_TIMEZONE = 'Africa/Algiers';

    public function __construct(
        private readonly DashboardMemberResolver $members = new DashboardMemberResolver,
    ) {}

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

        [$memberId, $memberDimension, $memberScopeIds, $memberLocked, $memberUnattributedOnly] = $this->members->resolve(
            $currentMembership,
            $input['memberId'] ?? null,
            $input['memberDimension'] ?? null
        );

        $carrierId = $this->resolveCarrier($input, $storeId);
        $dateBasis = $this->resolveDateBasis($input['dateBasis'] ?? null);
        $productIds = $this->resolveIdList($input['productIds'] ?? null);
        $variantIds = $this->resolveIdList($input['variantIds'] ?? null);

        // D3: a filter without a control in the active view is dropped from the
        // DTO (the Livewire property keeps its value, so switching view restores
        // it). Confirmation has no carrier picker; delivery has no product picker
        // and always answers on the order's own creation date.
        if ($memberDimension === 'delivery') {
            $productIds = null;
            $variantIds = null;
            $dateBasis = DashboardFilter::DATE_BASIS_CREATED;
        } else {
            $carrierId = null;
        }

        return new DashboardFilter(
            period: $period,
            from: $from?->utc(),
            to: $to?->utc(),
            timezone: $timezone,
            // Buckets are shifted by the offset in effect at the end of the window.
            utcOffsetSeconds: ($to ?? $now)->getOffset(),
            carrierId: $carrierId,
            memberId: $memberId,
            memberDimension: $memberDimension,
            memberScopeIds: $memberScopeIds,
            storeId: $storeId ?? '',
            memberLocked: $memberLocked,
            memberUnattributedOnly: $memberUnattributedOnly,
            dateBasis: $dateBasis,
            productIds: $productIds,
            variantIds: $variantIds,
        );
    }

    private function resolveDateBasis(?string $requested): string
    {
        return $requested === DashboardFilter::DATE_BASIS_LAST_STATUS
            ? DashboardFilter::DATE_BASIS_LAST_STATUS
            : DashboardFilter::DATE_BASIS_CREATED;
    }

    /**
     * A product/variant pick from the filter bar: a flat list of ids, deduped
     * and capped so a hand-edited URL cannot turn into an unbounded IN clause.
     * Absent or non-list input means "no product filter".
     *
     * @return ?array<int, string>
     */
    private function resolveIdList(mixed $value): ?array
    {
        if (! is_array($value)) {
            return null;
        }

        $ids = array_values(array_unique(array_filter(
            array_map(static fn ($id) => is_scalar($id) ? trim((string) $id) : '', $value),
            static fn (string $id) => $id !== '' && $id !== '0'
        )));

        return $ids === [] ? null : array_slice($ids, 0, 100);
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
}
