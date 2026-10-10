<?php

namespace App\Console\Commands;

use App\Domains\Status\Support\OrderStatusStage;
use App\Models\Stores\Store;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * PHASE 38-C.1 — read-only capture-health checks.
 *
 * Scans, per store, the orders/status histories/trackings created or changed
 * since the store's finance_capture_started_at and reports whether the
 * first-write-wins stamps (PHASE 38-C) are absent where they must exist.
 *
 * Read-only by contract: nothing here writes to the database or heals a row.
 * The checks marked "must be 0" assert invariant violations (a bypass path,
 * a lost provenance stamp, an unmapped system status); the informational
 * ones exist so the merchant sees what needs a mapping decision in 38-B/38-C
 * (unattributed manual/bulk confirmations, manual deliveries without carrier
 * evidence, rider/carrier trackings without an actor, custom statuses in use).
 *
 * Exit code: non-zero (FAILURE) while any "must be 0" check fails for any
 * scanned store; SUCCESS only when every such check is clean.
 */
class FinanceCaptureHealth extends Command
{
    protected $signature = 'finance:capture-health
        {--store= : Limit the scan to one store (id or slug)}
        {--since= : Only orders/histories/trackings created or changed on/after this datetime (defaults to the store\'s finance_capture_started_at)}';

    protected $description = 'Report 38-C capture-health invariants per store; exits non-zero on any policy violation';

    /** Stages that evidence a confirmed sale — the "confirmed-or-later" bucket. */
    private const CONFIRMED_OR_LATER = [
        OrderStatusStage::CONFIRMED,
        OrderStatusStage::IN_DELIVERY,
        OrderStatusStage::DELIVERED,
        OrderStatusStage::RETURNED,
    ];

    public function handle(): int
    {
        $sinceOverride = $this->option('since');
        $since = null;

        if ($sinceOverride !== null) {
            try {
                $since = Carbon::parse((string) $sinceOverride);
            } catch (\Throwable) {
                $since = null;
            }

            if ($since === null) {
                $this->error("--since must be a parseable datetime, got [{$sinceOverride}].");

                return static::FAILURE;
            }
        }

        $stores = $this->resolveStores((string) ($this->option('store') ?? ''));

        if ($stores->isEmpty()) {
            return static::FAILURE;
        }

        $storesWithViolations = 0;

        foreach ($stores as $store) {
            $storesWithViolations += $this->scanStore($store, $since);
        }

        $this->newLine();

        if ($storesWithViolations > 0) {
            $this->error("Capture health: {$storesWithViolations} store(s) report violations.");

            return static::FAILURE;
        }

        $this->info('Capture health: all scanned store(s) are healthy.');

        return static::SUCCESS;
    }

    protected function scanStore(Store $store, ?Carbon $sinceOverride): int
    {
        $since = $sinceOverride ?? $store->settings?->finance_capture_started_at;

        $confirmedMissing = $this->confirmedMissing($store->id, $since);
        $attribution = $this->unattributedBySource($confirmedMissing['ids']);
        $manualOrBulkUnattributed = ($attribution['manual'] ?? 0) + ($attribution['bulk'] ?? 0);

        $deliveredMissing = $this->deliveredMissing($store->id, $since);
        $returnedMissing = $this->returnedMissing($store->id, $since);
        $historyViolations = $this->historyViolations($store->id, $since);

        $evidenceBySource = $this->evidenceMissingBySource($store->id, $since);
        $trackingByPath = $this->trackingActorByPath($store->id, $since);
        $otherStatuses = $this->otherStageStatuses($store->id, $since);
        $systemStageMissing = $this->systemStageMissing($store->id);

        $mustBeZero = [
            $confirmedMissing['count'],
            $manualOrBulkUnattributed,
            $deliveredMissing,
            $returnedMissing,
            $historyViolations,
            $systemStageMissing,
        ];

        $violations = count(array_filter($mustBeZero, static fn (int $n): bool => $n > 0));

        $this->newLine();
        $this->info(sprintf(
            'Store: %s (%s) — capture since %s',
            $store->name,
            $store->slug,
            $since ? $since->toDateTimeString() : 'unbounded (no finance_capture_started_at)',
        ));

        $this->table(
            ['Check', 'Count', 'Policy', 'Status'],
            [
                ['1. Confirmed-or-later without confirmed_at', $confirmedMissing['count'], 'must be 0', $this->badge($confirmedMissing['count'])],
                ['2. …with NULL actor — manual + bulk', $manualOrBulkUnattributed, 'must be 0', $this->badge($manualOrBulkUnattributed)],
                ['3. Delivered without delivered_at', $deliveredMissing, 'must be 0', $this->badge($deliveredMissing)],
                ['4. Returned without returned_at', $returnedMissing, 'must be 0', $this->badge($returnedMissing)],
                ['5. Histories with NULL source / from_status', $historyViolations, 'must be 0', $this->badge($historyViolations)],
                ['6. Trackings without creator', array_sum($trackingByPath), 'informational', '—'],
                ['7. “other”-stage statuses in use', count($otherStatuses), 'informational', '—'],
                ['8. System statuses without stage', $systemStageMissing, 'must be 0', $this->badge($systemStageMissing)],
            ],
        );

        if (! empty($attribution)) {
            $total = array_sum($attribution);
            $rows = collect($attribution)
                ->map(fn (int $count, string $source): array => [$source, $count, $this->percent($count, $total)])
                ->values()
                ->all();

            $this->newLine();
            $this->info('2. Unattributed confirmations by history source');
            $this->table(['Source', 'Count', '% of affected'], $rows);
        }

        if (! empty($evidenceBySource)) {
            $this->newLine();
            $this->info('3b. Delivered without delivery evidence, by delivered transition source');
            $this->table(
                ['Source', 'Count'],
                collect($evidenceBySource)->map(fn (int $count, string $source): array => [$source, $count])->values()->all(),
            );
        }

        if (! empty($trackingByPath)) {
            $this->newLine();
            $this->info('6. Trackings without a creator, by creation path');
            $this->table(
                ['Path', 'Count'],
                collect($trackingByPath)->map(fn (int $count, string $path): array => [$path, $count])->values()->all(),
            );
        }

        if (! empty($otherStatuses)) {
            $this->newLine();
            $this->info('7. Statuses with stage “other” used by orders — map custom ones in 38-B');
            $this->table(
                ['Key', 'Label', 'Store'],
                collect($otherStatuses)->map(fn (array $row): array => [$row['key'], $row['label'], $store->name])->all(),
            );
        }

        return $violations > 0 ? 1 : 0;
    }

    /* =========================
     | Metric queries
     ========================= */

    /**
     * Orders currently in a confirmed-or-later stage but with no confirmed_at
     * stamp — read as a capture bypass path that skipped the CAS.
     *
     * @return array{count: int, ids: array<int, string>}
     */
    protected function confirmedMissing(int|string $storeId, ?Carbon $since): array
    {
        $ids = $this->orderScope($storeId, $since)
            ->whereIn('s.stage', self::CONFIRMED_OR_LATER)
            ->whereNull('o.confirmed_at')
            ->pluck('o.id')
            ->all();

        return ['count' => count($ids), 'ids' => array_values($ids)];
    }

    /**
     * For each affected order (confirmed-or-later, no confirmed_at), the source
     * of the earliest status history that moved the order into a confirmed-or-
     * later stage — the transition that should have captured the actor. Orders
     * with no usable history are bucketed `no-history`.
     *
     * @param  array<int, string>  $orderIds
     * @return array<string, int> source => count
     */
    protected function unattributedBySource(array $orderIds): array
    {
        if (empty($orderIds)) {
            return [];
        }

        $rows = DB::table('order_status_histories as h')
            ->join('statuses as hs', 'hs.id', '=', 'h.status_id')
            ->whereIn('h.order_id', $orderIds)
            ->whereIn('hs.stage', self::CONFIRMED_OR_LATER)
            ->orderBy('h.created_at')
            ->orderBy('h.id')
            ->get(['h.order_id', 'h.source']);

        $earliest = [];

        foreach ($rows as $row) {
            if (! array_key_exists($row->order_id, $earliest)) {
                $earliest[$row->order_id] = $row->source ?? 'unattributed';
            }
        }

        foreach ($orderIds as $orderId) {
            if (! array_key_exists($orderId, $earliest)) {
                $earliest[$orderId] = 'no-history';
            }
        }

        return array_count_values($earliest);
    }

    protected function deliveredMissing(int|string $storeId, ?Carbon $since): int
    {
        return (int) $this->orderScope($storeId, $since)
            ->where('s.stage', OrderStatusStage::DELIVERED)
            ->whereNull('o.delivered_at')
            ->count();
    }

    protected function returnedMissing(int|string $storeId, ?Carbon $since): int
    {
        return (int) $this->orderScope($storeId, $since)
            ->where('s.stage', OrderStatusStage::RETURNED)
            ->whereNull('o.returned_at')
            ->count();
    }

    /**
     * Delivered orders whose delivered timestamp is present but the carrier
     * evidence stamp is missing, split by the source of their `delivered`
     * transition history. Informational: manual deliveries legitimately have
     * no carrier evidence (the carrier CAS is only fired by carrier events).
     *
     * @return array<string, int> source => count
     */
    protected function evidenceMissingBySource(int|string $storeId, ?Carbon $since): array
    {
        $orderIds = $this->orderScope($storeId, $since)
            ->where('s.stage', OrderStatusStage::DELIVERED)
            ->whereNotNull('o.delivered_at')
            ->whereNull('o.delivery_evidence_at')
            ->pluck('o.id')
            ->all();

        $sources = [];

        foreach ($orderIds as $orderId) {
            $history = DB::table('order_status_histories as h')
                ->join('statuses as hs', 'hs.id', '=', 'h.status_id')
                ->where('h.order_id', $orderId)
                ->where('hs.key', 'delivered')
                ->orderByDesc('h.created_at')
                ->orderByDesc('h.id')
                ->first(['h.source']);

            $sources[] = $history?->source ?? 'unattributed';
        }

        return array_count_values($sources);
    }

    /**
     * Status-histories (since the capture start) whose provenance is broken:
     * a NULL source (the observer always writes a source bucket, defaulting to
     * `system`) or a NULL from_status on anything but the order's earliest
     * (creation) row. ULIDs are time-sortable, so min(id) is the creation row.
     */
    protected function historyViolations(int|string $storeId, ?Carbon $since): int
    {
        $violations = 0;

        $grouped = DB::table('order_status_histories as h')
            ->join('orders as o', 'o.id', '=', 'h.order_id')
            ->where('o.store_id', $storeId)
            ->whereNull('o.deleted_at')
            ->when($since, fn ($q) => $q->where('h.created_at', '>=', $since))
            ->get(['h.id', 'h.order_id', 'h.source', 'h.from_status'])
            ->groupBy('order_id');

        foreach ($grouped as $histories) {
            $creationRowId = $histories->min('id');

            foreach ($histories as $history) {
                if ($history->source === null) {
                    $violations++;

                    continue;
                }

                if ($history->from_status === null && (string) $history->id !== (string) $creationRowId) {
                    $violations++;
                }
            }
        }

        return $violations;
    }

    /**
     * Trackings of in-scope orders created without an actor, split by creation
     * path. `order_trackings` has no source column, so the path is derived: the
     * source of the order's `shipped` status history when one exists (the
     * OrderObserver syncTracking path), otherwise whether the shipment carries
     * a carrier provider (`carrier:direct`) or not (`rider:direct`).
     *
     * @return array<string, int> path => count
     */
    protected function trackingActorByPath(int|string $storeId, ?Carbon $since): array
    {
        $orderIds = $this->orderScope($storeId, $since)->pluck('o.id')->all();

        if (empty($orderIds)) {
            return [];
        }

        $trackings = DB::table('order_trackings as ot')
            ->whereIn('ot.order_id', $orderIds)
            ->whereNull('ot.tracked_by_membership_id')
            ->get(['ot.order_id', 'ot.shipping_provider_id']);

        $paths = [];

        foreach ($trackings as $tracking) {
            $history = DB::table('order_status_histories as h')
                ->join('statuses as hs', 'hs.id', '=', 'h.status_id')
                ->where('h.order_id', $tracking->order_id)
                ->where('hs.key', 'shipped')
                ->orderByDesc('h.created_at')
                ->orderByDesc('h.id')
                ->first(['h.source']);

            $path = $history !== null
                ? 'order:transit:'.($history->source ?? 'unattributed')
                : ($tracking->shipping_provider_id !== null ? 'carrier:direct' : 'rider:direct');

            $paths[$path] = ($paths[$path] ?? 0) + 1;
        }

        return $paths;
    }

    /**
     * Custom order statuses (stage `other`) used by at least one in-scope
     * order. Informational — the merchant must map them to a lifecycle bucket
     * in 38-B, otherwise their stage never evidences anything.
     *
     * @return array<int, array{key: string, label: string}>
     */
    protected function otherStageStatuses(int|string $storeId, ?Carbon $since): array
    {
        return $this->orderScope($storeId, $since)
            ->where('s.stage', OrderStatusStage::OTHER)
            ->distinct()
            ->get(['s.key', 's.label'])
            ->map(fn ($row): array => ['key' => $row->key, 'label' => $row->label ?? ''])
            ->all();
    }

    /**
     * System statuses that apply to the store (its own rows or the global
     * ones) but carry no stage. A global status without a stage would be
     * reported for every store, which is the point — it is shared schema.
     */
    protected function systemStageMissing(int|string $storeId): int
    {
        // `statuses.stage` is NOT NULL (default `other`), so a missing stage can
        // never be observed as NULL — a system ORDER status that "lacks a stage"
        // sits on the fallback bucket while the resolver disagrees (drift between
        // the row and OrderStatusStage::forKey, which re-seeding would realign).
        $rows = DB::table('statuses')
            ->where('is_system', true)
            ->where('type', 'order')
            ->where(fn ($q) => $q->where('store_id', $storeId)->orWhereNull('store_id'))
            ->get(['id', 'key', 'stage']);

        $drifted = 0;

        foreach ($rows as $status) {
            if ($status->stage !== OrderStatusStage::forKey($status->key)) {
                $drifted++;
            }
        }

        return $drifted;
    }

    /* =========================
     | Helpers
     ========================= */

    /** Orders (not deleted, created or changed since @since) joined to their current status. */
    protected function orderScope(int|string $storeId, ?Carbon $since)
    {
        return DB::table('orders as o')
            ->join('statuses as s', 's.id', '=', 'o.status_id')
            ->where('o.store_id', $storeId)
            ->whereNull('o.deleted_at')
            ->when($since, fn ($q) => $q->where(fn ($w) => $w
                ->where('o.created_at', '>=', $since)
                ->orWhere('o.updated_at', '>=', $since)));
    }

    protected function badge(int $count): string
    {
        return $count > 0 ? 'VIOLATION' : 'OK';
    }

    protected function percent(int $part, int $total): string
    {
        if ($total <= 0) {
            return '—';
        }

        return round(($part / $total) * 100, 1).'%';
    }

    protected function resolveStores(string $selector)
    {
        $query = Store::query();

        if ($selector !== '') {
            $query->where(fn ($q) => $q->where('id', $selector)->orWhere('slug', $selector));

            $stores = $query->orderBy('name')->get();

            if ($stores->isEmpty()) {
                $this->error("Store [{$selector}] not found.");

                return collect();
            }

            return $stores;
        }

        return $query->orderBy('name')->get();
    }
}
