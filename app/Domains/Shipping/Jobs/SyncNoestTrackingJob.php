<?php

namespace App\Domains\Shipping\Jobs;

use App\Domains\Shipping\Models\ShippingProvider;
use App\Domains\Shipping\Services\NoestTrackingMapper;
use App\Domains\Shipping\Services\StopdeskOfficeSync;
use App\Enums\Store\OrderTrackingStatus;
use App\Models\Orders\OrderTracking;
use App\Models\Orders\OrderTrackingHistory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Polls NOEST's /get/trackings/info for every open (or not-yet-synced) tracking
 * record of a store's NOEST-backed providers and advances the local
 * order_trackings statuses. Idempotent: terminal records are only refreshed,
 * never regressed, and a History row is appended per status transition.
 */
class SyncNoestTrackingJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 300;

    public int $tries = 1;

    public function __construct(public string $storeId) {}

    public function handle(?StopdeskOfficeSync $resolver = null): void
    {
        $resolver ??= app(StopdeskOfficeSync::class);

        $providers = ShippingProvider::query()
            ->with('carrier')
            ->where('store_id', $this->storeId)
            ->where('is_active', true)
            ->whereHas('carrier', fn ($query) => $query->where('code', 'noest'))
            ->get();

        foreach ($providers as $provider) {
            $this->syncProvider($resolver, $provider);
        }
    }

    private function syncProvider(StopdeskOfficeSync $resolver, ShippingProvider $provider): void
    {
        $adapter = $resolver->resolve($provider);

        if (! $adapter || ! method_exists($adapter, 'trackingsInfo')) {
            return;
        }

        $base = $this->dueTrackings($provider);
        if ($base->isEmpty()) {
            return;
        }

        foreach ($base->chunk(20) as $chunk) {
            try {
                $data = $adapter->trackingsInfo($provider, $chunk->pluck('tracking_number')->all());
            } catch (\Throwable $e) {
                report($e);
                continue;
            }

            if (! is_array($data) || $data === []) {
                $this->touchSyncedAt($chunk);
                continue;
            }

            $byNumber = $chunk->keyBy('tracking_number');

            foreach ($data as $trackingNumber => $entry) {
                if (! is_array($entry)) {
                    continue;
                }

                $tracking = $byNumber->get((string) $trackingNumber);
                if (! $tracking) {
                    continue;
                }

                $this->apply($tracking, $entry);
            }

            $this->touchSyncedAt($byNumber);
        }
    }

    /**
     * Rows that should be refreshed: not yet in a terminal state, or never
     * synced, or synced more than RESYNC_MINUTES ago.
     */
    private function dueTrackings(ShippingProvider $provider)
    {
        return OrderTracking::query()
            ->where('shipping_provider_id', $provider->id)
            ->whereNotNull('tracking_number')
            ->where('tracking_number', '!=', '')
            ->where(function ($query) {
                $query->whereNull('delivered_at')
                    ->whereNull('returned_at')
                    ->orWhereNull('last_synced_at')
                    ->orWhere('last_synced_at', '<', now()->subMinutes(15));
            })
            ->get();
    }

    private function apply(OrderTracking $tracking, array $entry): void
    {
        $orderInfo = $entry['OrderInfo'] ?? [];
        $activity = $entry['activity'] ?? [];

        if (! is_array($orderInfo) || ! is_array($activity) || $activity === []) {
            return;
        }

        $events = $this->orderedEvents($activity);
        $latest = end($events);

        if ($latest === false) {
            return;
        }

        $eventKey = is_string($latest['event_key'] ?? null) && $latest['event_key'] !== ''
            ? $latest['event_key']
            : null;
        $eventText = is_string($latest['event'] ?? null) ? $latest['event'] : null;

        $status = $eventKey !== null
            ? NoestTrackingMapper::toStatus($eventKey)
            : NoestTrackingMapper::eventTextToStatus($eventText);

        if ($status === null && $eventText !== null) {
            $status = NoestTrackingMapper::eventTextToStatus($eventText);
        }

        if ($status === null) {
            return;
        }

        $deliveredAt = $status === OrderTrackingStatus::DELIVERED
            ? $this->eventDate($events, [OrderTrackingStatus::DELIVERED->value, 'livre', 'livred'])
            : null;
        $returnedAt = $status === OrderTrackingStatus::RETURNED
            ? $this->eventDate($events, NoestTrackingMapper::terminalKeys(OrderTrackingStatus::RETURNED))
            : null;

        $previous = OrderTrackingStatus::tryFrom((string) $tracking->tracking_status);

        $updates = [
            'carrier_status' => $eventKey ?? (mb_substr((string) $eventText, 0, 100) ?: null),
            'carrier_label' => $eventText,
            'tracking_status' => $status->value,
            'carrier_raw' => $entry,
            'last_synced_at' => now(),
        ];

        if (! $tracking->shipped_at && $events !== []) {
            $updates['shipped_at'] = $events[0]['date'] ?? now();
        }

        if ($deliveredAt) {
            $updates['delivered_at'] = $deliveredAt;
        }

        if ($returnedAt) {
            $updates['returned_at'] = $returnedAt;
        }

        $tracking->update($updates);

        if ($previous === null || $previous->value !== $status->value) {
            OrderTrackingHistory::create([
                'store_id' => $tracking->store_id,
                'order_id' => $tracking->order_id,
                'order_tracking_id' => $tracking->id,
                'status' => $status->value,
                'payload' => [
                    'carrier_sync' => true,
                    'event' => $eventText,
                    'event_key' => $eventKey,
                    'previous_status' => $previous?->value,
                ],
            ]);
        }
    }

    /**
     * Chronologically ascending activity rows, ignoring entries we cannot read.
     */
    private function orderedEvents(array $activity): array
    {
        $events = [];

        foreach ($activity as $row) {
            if (! is_array($row)) {
                continue;
            }

            $date = isset($row['date']) ? \Illuminate\Support\Carbon::parse((string) $row['date']) : null;

            if (! $date) {
                continue;
            }

            $events[] = [
                'event' => isset($row['event']) ? (string) $row['event'] : null,
                'event_key' => isset($row['event_key']) ? (string) $row['event_key'] : null,
                'date' => $date,
            ];
        }

        usort($events, fn ($a, $b) => $a['date']->timestamp <=> $b['date']->timestamp);

        return $events;
    }

    private function eventDate(array $events, array $keys): ?\Illuminate\Support\Carbon
    {
        foreach ($events as $event) {
            if ($event['event_key'] !== null && in_array($event['event_key'], $keys, true)) {
                return $event['date'];
            }
        }

        $latest = end($events);

        return $latest === false ? null : $latest['date'];
    }

    private function touchSyncedAt($trackings): void
    {
        if (! $trackings instanceof \Illuminate\Support\Collection || $trackings->isEmpty()) {
            return;
        }

        OrderTracking::query()
            ->whereIn('id', $trackings->pluck('id')->all())
            ->update(['last_synced_at' => now()]);

        $trackings->each(fn ($tracking) => $tracking->last_synced_at = now());
    }
}