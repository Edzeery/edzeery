<?php

namespace App\Domains\Shipping\Services;

use App\Enums\Store\OrderTrackingStatus;
use App\Models\Orders\OrderTracking;
use App\Models\Orders\OrderTrackingHistory;
use Illuminate\Support\Carbon;

/**
 * Single source of truth for advancing local order_trackings statuses from a
 * carrier's /get/trackings/info response. Shared by SyncNoestTrackingJob (the
 * scheduled bulk poll) and the merchant tracking page "Update now" action so
 * every write to a tracking row follows the same idempotent rules.
 */
class NoestTrackingSyncService
{
    public function syncOne(OrderTracking $tracking): array
    {
        $provider = $tracking->shippingProvider;

        if (! $provider) {
            return ['ok' => false, 'error' => 'no_provider'];
        }

        if (! $tracking->tracking_number) {
            return ['ok' => false, 'error' => 'no_number'];
        }

        $adapter = (new StopdeskOfficeSync())->resolve($provider);

        if (! $adapter || ! method_exists($adapter, 'trackingsInfo')) {
            return ['ok' => false, 'error' => 'unsupported_carrier'];
        }

        try {
            $data = $adapter->trackingsInfo($provider, [(string) $tracking->tracking_number]);
        } catch (\Throwable $e) {
            report($e);

            return ['ok' => false, 'error' => 'request_failed'];
        }

        $entry = $data[(string) $tracking->tracking_number] ?? null;

        if (! is_array($entry)) {
            return ['ok' => false, 'error' => 'no_data'];
        }

        $this->apply($tracking, $entry);

        return ['ok' => true];
    }

    /**
     * Idempotent: terminal records are only refreshed, never regressed, and a
     * History row is appended per status transition only.
     */
    public function apply(OrderTracking $tracking, array $entry): void
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
            // A valid carrier response was parsed even if no status mapping matched:
            // the row was still successfully polled, so it counts as synced. When the
            // row never had a status we fall back to a safe IN_TRANSIT so a polled
            // shipment is never left blank.
            $updates = ['last_synced_at' => now()];

            if (blank($tracking->tracking_status)) {
                $updates['tracking_status'] = OrderTrackingStatus::IN_TRANSIT->value;

                OrderTrackingHistory::create([
                    'store_id'          => $tracking->store_id,
                    'order_id'          => $tracking->order_id,
                    'order_tracking_id' => $tracking->id,
                    'status'            => OrderTrackingStatus::IN_TRANSIT->value,
                    'payload'           => ['carrier_sync' => true, 'fallback_in_transit' => true],
                ]);
            }

            $tracking->update($updates);

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

    private function orderedEvents(array $activity): array
    {
        $events = [];

        foreach ($activity as $row) {
            if (! is_array($row)) {
                continue;
            }

            $date = isset($row['date']) ? Carbon::parse((string) $row['date']) : null;

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

    private function eventDate(array $events, array $keys): ?Carbon
    {
        foreach ($events as $event) {
            if ($event['event_key'] !== null && in_array($event['event_key'], $keys, true)) {
                return $event['date'];
            }
        }

        $latest = end($events);

        return $latest === false ? null : $latest['date'];
    }
}