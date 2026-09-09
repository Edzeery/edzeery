<?php

namespace App\Domains\Shipping\Services;

use App\Enums\Store\OrderTrackingStatus;

/**
 * Maps a raw NOEST tracking activity event (its `event_key`) onto our internal
 * OrderTrackingStatus. Events outside the mapped set (financial edits, suspended
 * but not resolved, etc.) return null so the sync leaves the previous status
 * untouched instead of guessing.
 */
final class NoestTrackingMapper
{
    /** @var array<string, list<string>> event_key => terminal/meaningful keys per status */
    private const MAP = [
        OrderTrackingStatus::SHIPPED->value => [
            'upload',
            'customer_validation',
        ],
        OrderTrackingStatus::IN_TRANSIT->value => [
            'validation_collect_colis',       // Package picked up from partner
            'validation_reception_admin',     // Reception validated by admin
            'validation_reception',           // Picked up by driver
            'sent_to_redispatch',             // Sent for redispatch
            'annulation_dispatch_retour',     // Return transmission cancelled
            'cancel_return_dispatched_to_partenaire',
        ],
        OrderTrackingStatus::OUT_FOR_DELIVERY->value => [
            'fdr_activated',                    // Delivery route activated
            'nouvel_tentative_asked_by_customer',
            'return_redispatched_to_livraison', // Return put back for delivery
        ],
        OrderTrackingStatus::FAILED_ATTEMPT->value => [
            'mise_a_jour',                    // Delivery attempt
        ],
        OrderTrackingStatus::RETURNING->value => [
            'return_asked_by_customer',
            'return_asked_by_hub',
            'return_dispatched_to_warehouse',
        ],
        OrderTrackingStatus::RETURNED->value => [
            'retour_dispatched_to_partenaires',   // Dispatched back to partner
            'return_dispatched_to_partenaire',
            'colis_retour_transmit_to_partner',   // Handed to partner
            'livraison_echoue_recu',              // Return received by partner
            'return_validated_by_partener',
            'pickedup',                           // Pick-up collected from customer
            'valid_return_pickup',
            'pickup_picked_recu',
        ],
        OrderTrackingStatus::DELIVERED->value => [
            'livre',
            'livred',
        ],
    ];

    public static function toStatus(?string $eventKey): ?OrderTrackingStatus
    {
        if ($eventKey === null || $eventKey === '') {
            return null;
        }

        foreach (self::MAP as $status => $keys) {
            if (in_array($eventKey, $keys, true)) {
                return OrderTrackingStatus::from($status);
            }
        }

        return null;
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
        return self::MAP[$status->value] ?? [];
    }
}