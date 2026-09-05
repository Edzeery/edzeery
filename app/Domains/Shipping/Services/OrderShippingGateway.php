<?php

namespace App\Domains\Shipping\Services;

use App\Domains\Order\Services\OrderAuditService;
use App\Domains\Order\Services\OrderService;
use App\Domains\Shipping\Models\ShippingProvider;
use App\Models\Orders\Order;
use App\Models\Stores\Team\StoreMembership;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * بوابة الإرسال الموحدة: «تأكيد → تجهيز → شحن (إرسال)» مع دفع الطلبية لشركة
 * التوصيل المتكاملة (NOEST...) وتسجيل الأحداث. مستخدمة من درج التأكيد
 * وشريط الإجراءات الجماعية (كان المنطق السابق داخل bulkSendToCarrier).
 */
class OrderShippingGateway
{
    public function __construct(
        protected OrderService $orders,
        protected CarrierOrderPostService $poster,
        protected OrderAuditService $audit,
    ) {
    }

    /**
     * @return array{
     *     order: Order,
     *     posted: bool,
     *     error: ?string,
     *     tracking_number: ?string,
     *     provider: ?ShippingProvider,
     * }
     */
    public function send(
        Order $order,
        ?string $providerId = null,
        ?string $reason = null,
        ?StoreMembership $changedBy = null,
        bool $confirmFirst = false,
    ): array {
        DB::beginTransaction();

        try {
            $order->refresh();

            $provider = null;
            if ($providerId) {
                $provider = ShippingProvider::query()
                    ->where('store_id', $order->store_id)
                    ->find($providerId);

                if (! $provider) {
                    throw new \InvalidArgumentException('Invalid shipping provider.');
                }

                // Provider must be set BEFORE the shipped transition — the
                // observer's syncTracking() reads order.shipping_provider_id
                // when creating the tracking record.
                $order->update(['shipping_provider_id' => $provider->id]);
                $order->unsetRelation('shippingProvider');
                $order->unsetRelation('status');
            }

            if ($confirmFirst && in_array($order->status?->key, ['pending', 'draft'], true)) {
                $order = $this->orders->transition($order, 'confirmed', $reason, $changedBy);
            }

            foreach (['preparing', 'shipped'] as $target) {
                if ($order->status?->key === $target) {
                    continue;
                }

                if (! $this->orders->canTransition($order, $target)) {
                    break;
                }

                $order = $this->orders->transition($order, $target, $reason, $changedBy);
            }

            $error = null;
            $posted = false;
            $trackingNumber = null;

            if ($order->shippingProvider) {
                try {
                    $tracking = $this->poster->postToCarrier($order);
                    $trackingNumber = $tracking?->tracking_number;
                    $posted = true;
                } catch (\Exception $e) {
                    Log::warning("carrier post failed for order [{$order->number}]: " . $e->getMessage());
                    $error = $e->getMessage();
                }
            }

            $this->audit->sentToCarrier(
                $order,
                $order->shippingProvider?->name,
                $trackingNumber,
                $changedBy,
            );

            DB::commit();

            return [
                'order' => $order->fresh(),
                'posted' => $posted,
                'error' => $error,
                'tracking_number' => $trackingNumber,
                'provider' => $provider ?? $order->shippingProvider,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}