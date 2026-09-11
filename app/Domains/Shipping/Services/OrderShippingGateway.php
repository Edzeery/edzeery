<?php

namespace App\Domains\Shipping\Services;

use App\Domains\Order\Services\OrderAuditService;
use App\Domains\Order\Services\OrderService;
use App\Domains\Order\Services\OrderTrackingService;
use App\Domains\Shipping\Models\ShippingProvider;
use App\Enums\Store\OrderTrackingStatus;
use App\Models\Orders\Order;
use App\Models\Stores\Team\StoreMembership;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * بوابة الإرسال الموحدة: «تأكيد → تجهيز → شحن (إرسال)» مع دفع الطلبية لشركة
 * التوصيل المتكاملة (NOEST...) وتسجيل الأحداث. مستخدمة من درج التأكيد
 * والإرسال المباشر/الجماعي (شريط الإجراءات).
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
     *     rate_note: ?string,
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

            $missing = app(\App\Domains\Order\Services\OrderCompleteness::class)->missing($order, true);

            if ($missing !== []) {
                throw \App\Domains\Order\Exceptions\OrderIncompleteException::fromMissing($missing);
            }

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

            if ($confirmFirst && in_array($order->status?->key, ['pending', 'draft', 'on_hold'], true)) {
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
                'rate_note' => $this->resolveRateNote($order),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * إلغاء الشحنة قبل الاكسبيديشن (cancel-send): حذف الطلبية من شركة التوصيل
     * عبر API + إلغاء تعيين رجل التوصيل، ثم إعادة الطلبية إلى حالة «confirmed»
     * لتعود للظهور في قائمة الطلبيات وتُعاد معالجتها.
     *
     * القواعد:
     * - غير قابلة للإلغاء بعد حالة «تأكيد شركة التوصيل» لشحنة الشركة
     *   (order_trackings.carrier_validated_at) — حذف NOEST ممنوع بعد التحقق.
     * - حذف الشركة يتم عبر أدابتور يملك deleteOrder؛ الشركات بلا دعم تُرفض هنا
     *   (لا حذف محلي زائف للشحنة المسجلة لدى شركة).
     * - سجل التتبع يبقى للتدقيق مع رقم تتبع محذوف + سطر تاريخ cancelled، بينما
     *   الغرض: لا يرتبط الاوردر بشحنة ميتة بعد الآن.
     *
     * @return array{ok: bool, order?: Order, error?: string}
     */
    public function cancel(
        Order $order,
        ?string $reason = null,
        ?StoreMembership $changedBy = null,
    ): array {
        DB::beginTransaction();

        try {
            $order->refresh();

            $statusKey = $order->status?->key;
            if (! in_array($statusKey, ['shipped', 'in_transit', 'out_for_delivery'], true)) {
                throw new \DomainException(__('order_flow.shipment_cancellation_status'));
            }

            $trackingService = app(\App\Domains\Order\Services\OrderTrackingService::class);
            $tracking = $trackingService->currentTracking($order);

            // Carrier leg — delete the unvalidated shipment at the carrier.
            if ($order->shipping_provider_id && $tracking?->tracking_number) {
                if ($tracking->isCarrierValidated()) {
                    throw new \DomainException(__('order_flow.shipment_already_validated'));
                }

                $provider = $order->shippingProvider;
                $carrier = $provider?->carrier;
                $adapterClass = $carrier
                    ? config(
                        "delivery.carrier_integrations.{$carrier->code}",
                        config('delivery.carrier_integrations.*'),
                    )
                    : null;

                if (! $adapterClass || ! class_exists($adapterClass) || ! method_exists($adapterClass, 'deleteOrder')) {
                    throw new \DomainException(__('order_flow.carrier_delete_not_supported'));
                }

                $result = app($adapterClass)->deleteOrder($provider, (string) $tracking->tracking_number);

                if (! ($result['ok'] ?? false)) {
                    throw new \DomainException(
                        (string) ($result['message'] ?? __('order_flow.shipment_cancellation_failed'))
                    );
                }

                Log::info("shipment cancelled at carrier for order [{$order->number}] (tracking {$tracking->tracking_number})");
            }

            // Rider leg — unassign the delivery rider.
            if ($order->delivery_rider_id) {
                $order->update(['delivery_rider_id' => null]);
                $order->unsetRelation('deliveryRider');
            }

            // Keep the tracking row for audit but detach the live carrier number
            // and mark the leg cancelled so the order no longer syncs the dead
            // shipment via the carrier polling.
            if ($tracking) {
                $sentTrackingNumber = $tracking->tracking_number;
                $previousStatus = $tracking->tracking_status;

                $tracking->update([
                    'tracking_number' => null,
                    'carrier_status' => 'cancelled',
                    'tracking_status' => null,
                ]);

                \App\Models\Orders\OrderTrackingHistory::create([
                    'store_id'                 => $tracking->store_id,
                    'order_id'                 => $tracking->order_id,
                    'order_tracking_id'        => $tracking->id,
                    'status'                   => 'cancelled',
                    'changed_by_membership_id' => $changedBy?->id,
                    'notes'                    => $reason,
                    'payload'                  => [
                        'shipment_cancelled' => true,
                        'tracking_number' => $sentTrackingNumber,
                        'previous_tracking_status' => $previousStatus,
                        'previous_order_status' => $statusKey,
                    ],
                    'created_at'               => now(),
                ]);

                $this->audit->tracking(
                    $order,
                    'cancelled',
                    $sentTrackingNumber,
                    $changedBy,
                );
            }

            // Return the order to 'confirmed' via the internal revert (bypasses
            // the public workflow — shipped→confirmed is not a user-facing
            // transition). Inventory stays reserved: 'confirmed' keeps the
            // existing RESERVE leg and never double-applies.
            $this->orders->revertTo($order, 'confirmed', $reason, $changedBy);

            DB::commit();

            return [
                'ok' => true,
                'order' => $order->fresh(),
            ];
        } catch (\DomainException $e) {
            DB::rollBack();

            return ['ok' => false, 'error' => $e->getMessage()];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::warning("shipment cancel failed for order [{$order->number}]: " . $e->getMessage());

            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * يحذف الشحنة عند شركة التوصيل (NOEST...) لطلبية شُحنت بالناقل لكنها لم
     * تتحقق carrier-validated — يُستدعى قبل الحذف النهائي الدائم كي لا يبقى رقم
     * التتبع حيًا عند الشركة بعد طهارة السجل محليًا. فشل شركة التوصيل أو غياب
     * ساق ناقل (رجل/بلا ناقل) لا يمنع الحذف المحلي أبدًا: يُسجَّل ويُرمى
     * النتيجة للمتصل.
     *
     * @return array{
     *     ok: bool,
     *     skipped?: 'no_provider'|'no_tracking'|'validated'|'unsupported',
     *     message?: ?string,
     *     error?: string,
     *     tracking_number?: ?string,
     * }
     */
    public function deleteAtCarrier(Order $order): array
    {
        $order->refresh();

        if (! $order->shipping_provider_id || ! $order->shippingProvider) {
            return ['ok' => true, 'skipped' => 'no_provider'];
        }

        $tracking = app(OrderTrackingService::class)->currentTracking($order);

        if (! $tracking?->tracking_number) {
            return ['ok' => true, 'skipped' => 'no_tracking'];
        }

        // NOEST refuses to delete a carrier-validated order; never attempt it.
        if ($tracking->isCarrierValidated()) {
            return ['ok' => true, 'skipped' => 'validated'];
        }

        $carrier = $order->shippingProvider->carrier;
        $adapterClass = $carrier
            ? config(
                "delivery.carrier_integrations.{$carrier->code}",
                config('delivery.carrier_integrations.*'),
            )
            : null;

        if (! $adapterClass || ! class_exists($adapterClass) || ! method_exists($adapterClass, 'deleteOrder')) {
            return ['ok' => true, 'skipped' => 'unsupported'];
        }

        try {
            $result = app($adapterClass)->deleteOrder($order->shippingProvider, (string) $tracking->tracking_number);

            if (($result['ok'] ?? false) !== true) {
                Log::warning(
                    "carrier delete failed for order [{$order->number}] (tracking {$tracking->tracking_number}): ".($result['message'] ?? 'unknown error')
                );
            }

            return [
                'ok' => (bool) ($result['ok'] ?? false),
                'message' => $result['message'] ?? null,
                'tracking_number' => (string) $tracking->tracking_number,
            ];
        } catch (\Throwable $e) {
            Log::warning(
                "carrier delete threw for order [{$order->number}] (tracking {$tracking->tracking_number}): {$e->getMessage()}"
            );

            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Announces a warning for carrier-sent orders whose announced rate for the
     * order's state/commune is missing ('unpriced') or explicitly zero/empty
     * ('zero_cost'). Rider-sent and non-geo orders return null.
     */
    protected function resolveRateNote(Order $order): ?string
    {
        $providerId = $order->shipping_provider_id;
        if (! $providerId || ! $order->state_id) {
            return null;
        }

        $rate = \App\Domains\Shipping\Models\DeliveryRate::query()
            ->where('store_id', $order->store_id)
            ->where('shipping_provider_id', $providerId)
            ->where('state_id', $order->state_id)
            ->where('is_active', true)
            ->first();

        if (! $rate) {
            return 'unpriced';
        }

        $cost = $order->delivery_type === Order::DELIVERY_STOPDESK
            ? $rate->office_cost
            : $rate->home_cost;

        if ($cost === null && $order->city_id) {
            $override = \App\Domains\Shipping\Models\DeliveryRateCity::query()
                ->where('store_id', $order->store_id)
                ->where('shipping_provider_id', $providerId)
                ->where('state_id', $order->state_id)
                ->where('city_id', $order->city_id)
                ->first();
            $cost = $override?->home_cost;
        }

        if ($cost === null || $cost === '') {
            return 'unpriced';
        }

        return (float) $cost <= 0 ? 'zero_cost' : null;
    }
}