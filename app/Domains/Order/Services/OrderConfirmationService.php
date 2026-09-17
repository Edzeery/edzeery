<?php

declare(strict_types=1);

namespace App\Domains\Order\Services;

use App\Domains\Order\Exceptions\OrderIncompleteException;
use App\Domains\Order\Support\OrderCompleteness;
use App\Models\Orders\Order;
use App\Models\Status;
use App\Models\Stores\Team\StoreMembership;

/**
 * Owns the *confirmation* lifecycle of an order: the narrow "order" status
 * scope (pending → confirmed → preparing → …) and its completeness checks.
 *
 * This is the counterweight to OrderTrackingService. Everything that reads or
 * writes carrier/tracking keys belongs there; this service never touches them,
 * so the two scopes can no longer be cross-wired by accident.
 */
class OrderConfirmationService
{
    public function __construct(
        private readonly OrderCompleteness $completeness,
        private readonly OrderService $baseService,
    ) {
    }

    /**
     * The confirmation-pipeline status keys, in order of progression.
     * This is the single list that consumer code should use; it lives apart
     * from the carrier keys to keep the scopes separated.
     *
     * @return string[]
     */
    public static function confirmationKeys(): array
    {
        return ['pending', 'confirmed', 'preparing'];
    }

    /**
     * Confirm an order once it passes completeness. This is confirmation-only;
     * it never touches carrier/shipping state.
     */
    public function confirm(
        Order $order,
        ?string $reason = null,
        ?StoreMembership $changedBy = null,
    ): Order {
        $missing = $this->completeness->missing($order);

        if ($missing !== []) {
            throw OrderIncompleteException::fromMissing($missing);
        }

        return $this->baseService->transition($order, 'confirmed', $reason, $changedBy);
    }

    /**
     * Move a confirmed order into preparation (fulfilment begins). This is a
     * confirmation-pipeline key; shipping keys are OrderTrackingService's.
     */
    public function startPreparing(Order $order, ?StoreMembership $changedBy = null): Order
    {
        return $this->baseService->transition($order, 'preparing', null, $changedBy);
    }

    /**
     * True when the order's status key is part of the confirmation pipeline.
     */
    public static function isConfirmationKey(?string $statusKey): bool
    {
        return in_array($statusKey, self::confirmationKeys(), true);
    }
}
