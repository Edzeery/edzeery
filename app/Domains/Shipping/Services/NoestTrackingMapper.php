<?php

namespace App\Domains\Shipping\Services;

use App\Domains\Shipping\Support\CarrierStatusDictionary;
use App\Enums\Store\OrderTrackingStatus;

/**
 * Maps a raw NOEST tracking activity event (its `event_key`) onto our internal
 * OrderTrackingStatus. The authoritative NOEST vocabulary now lives in
 * CarrierStatusDictionary (shared with the merchant statuses screen); this
 * mapper stays as a thin NOEST-facing facade so the sync service keeps a single
 * unambiguous name.
 *
 * Events outside the dictionary (financial edits, informational updates, etc.)
 * return null so the sync leaves the previous status untouched instead of
 * guessing.
 */
final class NoestTrackingMapper
{
    public static function toStatus(?string $eventKey): ?OrderTrackingStatus
    {
        return CarrierStatusDictionary::statusFor('noest', $eventKey);
    }

    /**
     * Fall back to the human-readable event text (e.g. "Delivered") when a
     * payload entry lacks an `event_key`, reusing the enum's raw-text matcher.
     */
    public static function eventTextToStatus(?string $eventText): ?OrderTrackingStatus
    {
        return OrderTrackingStatus::fromCarrier($eventText);
    }

    /**
     * The raw event keys that mark a status as reached (used to stamp
     * delivered_at / returned_at with the exact carrier event date).
     */
    public static function terminalKeys(OrderTrackingStatus $status): array
    {
        return CarrierStatusDictionary::keysFor('noest', $status);
    }
}
