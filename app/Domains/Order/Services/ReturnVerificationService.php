<?php

namespace App\Domains\Order\Services;

use App\Enums\Store\ReturnInspectionResult;
use App\Models\Orders\Order;
use App\Models\Orders\OrderTracking;
use App\Models\Stores\Team\StoreMembership;
use Illuminate\Support\Facades\DB;

class ReturnVerificationService
{
    /**
     * Step 1: Scan/verify a returned package's barcode against an open
     * (returned, unverified) tracking record. The scanned code may match
     * either the assigned verification_barcode OR the carrier tracking_number.
     */
    public function verifyByCode(string $storeId, string $scannedCode, StoreMembership $verifiedBy): ?OrderTracking
    {
        $tracking = OrderTracking::where('store_id', $storeId)
            ->whereNotNull('returned_at')
            ->whereNull('verified_at')
            ->where(function ($q) use ($scannedCode) {
                $q->where('verification_barcode', $scannedCode)
                  ->orWhere('tracking_number', $scannedCode);
            })
            ->first();

        if (! $tracking) {
            return null;
        }

        $tracking->update([
            'verification_barcode'      => $tracking->verification_barcode ?? $scannedCode,
            'verified_at'               => now(),
            'verified_by_membership_id' => $verifiedBy->id,
        ]);

        return $tracking;
    }

    /**
     * Step 2: Record the physical inspection decision. Must happen after
     * verification. Does NOT requeue automatically.
     */
    public function process(
        OrderTracking $tracking,
        ReturnInspectionResult $result,
        ?string $notes,
        StoreMembership $processedBy,
    ): OrderTracking {
        if (! $tracking->verified_at) {
            throw new \DomainException('Cannot process a tracking record that has not been barcode-verified.');
        }

        $tracking->update([
            'inspection_result'          => $result->value,
            'inspection_notes'           => $notes,
            'processed_at'               => now(),
            'processed_by_membership_id' => $processedBy->id,
        ]);

        return $tracking;
    }

    /**
     * Step 3: Explicit merchant decision to resend the order to a new
     * confirmation cycle. Only allowed when inspection passed as 'good'.
     */
    public function requeue(OrderTracking $tracking, StoreMembership $requeuedBy): Order
    {
        if (! $tracking->processed_at) {
            throw new \DomainException('Cannot requeue before inspection is processed.');
        }

        $result = ReturnInspectionResult::tryFrom($tracking->inspection_result ?? '');

        if (! $result?->isRequeueEligible()) {
            throw new \DomainException('Only orders inspected as "good" can be requeued.');
        }

        if ($tracking->requeued_at) {
            throw new \DomainException('This return has already been requeued.');
        }

        return DB::transaction(function () use ($tracking, $requeuedBy) {
            $order = $tracking->order()->lockForUpdate()->firstOrFail();

            $order->update([
                'assigned_to_membership_id' => null,
                'assigned_at'               => null,
                'assignment_method'         => null,
                'assigned_by_membership_id' => null,
                'confirmation_attempts'     => 0,
            ]);

            app(OrderService::class)->transition(
                $order,
                'pending',
                'Requeued after return verification (good condition)',
                $requeuedBy,
            );

            $tracking->update([
                'requeued_at'               => now(),
                'requeued_by_membership_id' => $requeuedBy->id,
            ]);

            return $order->fresh();
        });
    }
}
