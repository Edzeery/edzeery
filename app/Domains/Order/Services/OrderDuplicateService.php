<?php

namespace App\Domains\Order\Services;

use App\Models\Orders\Order;
use Carbon\Carbon;

/**
 * كشف الطلبيات المحتملة المكررة/المتكررة: عميل نفس رقم الهاتف + منتجات مشابهة خلال ٣٠ يومًا.
 * لا يمنع الإرسال — إنذار فقط مع إمكانية وضع علامة.
 */
class OrderDuplicateService
{
    public const WINDOW_DAYS = 30;

    /** عدّ أقصى لل ». « في العرض. */
    public const MAX_RESULTS = 5;

    /**
     * قبول نموذج ملموس أو مصفوفة قادمة من نموذج الإنشاء/التعديل قبل الحفظ.
     *
     * @param  Order|array<string, mixed>  $candidate
     * @return array<int, array{order_id: string, number: string, created_at: string, status_key: string, items_overlap: int, total_overlap_qty: int}>
     */
    public function findSimilar(Order|array $candidate, int $limit = self::MAX_RESULTS): array
    {
        $isArray = is_array($candidate);

        $storeId = $isArray ? ($candidate['store_id'] ?? null) : $candidate->store_id;
        $excludeId = $isArray ? ($candidate['exclude_id'] ?? null) : $candidate->id;
        $phone = $isArray ? ($candidate['customer_phone'] ?? null) : $candidate->customer?->phone;

        $candidateItems = $isArray ? ($candidate['items'] ?? []) : $candidate->items;

        $variantIds = collect($candidateItems)
            ->map(fn($item) => is_array($item) ? ($item['product_variant_id'] ?? null) : $item->product_variant_id)
            ->filter()
            ->values()
            ->all();

        $productIds = collect($candidateItems)
            ->map(fn($item) => is_array($item) ? ($item['product_id'] ?? null) : $item->product_id)
            ->filter()
            ->values()
            ->all();

        if (empty($variantIds) && empty($productIds) && ! $phone) {
            return [];
        }

        $since = Carbon::now()->subDays(self::WINDOW_DAYS);

        $query = Order::query()
            ->where('store_id', $storeId)
            ->where('created_at', '>=', $since)
            ->with(['status', 'items']);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        if ($phone) {
            $query->whereHas('customer', fn($q) => $q->where('phone', $phone));
        }

        if (! empty($variantIds) || ! empty($productIds)) {
            $query->whereHas('items', function ($q) use ($variantIds, $productIds) {
                if (! empty($variantIds)) {
                    $q->whereIn('product_variant_id', $variantIds);
                }

                if (! empty($productIds)) {
                    $q->orWhereIn('product_id', $productIds);
                }
            });
        }

        $candidates = $query->limit($limit + 1)->get();

        $results = [];

        foreach ($candidates as $candidateOrder) {
            $items = $candidateOrder->items;

            $overlapVariant = $items->whereIn('product_variant_id', $variantIds)->count();
            $overlapProduct = $items->whereIn('product_id', $productIds)->count();
            $overlap = max($overlapVariant, $overlapProduct);

            $overlapQty = (int) $items
                ->filter(fn($item) => in_array($item->product_variant_id, $variantIds, true) || in_array($item->product_id, $productIds, true))
                ->sum('quantity');

            $results[] = [
                'order_id' => $candidateOrder->id,
                'number' => $candidateOrder->number,
                'created_at' => $candidateOrder->created_at->toIso8601String(),
                'status_key' => $candidateOrder->status?->key ?? '—',
                'items_overlap' => $overlap,
                'total_overlap_qty' => $overlapQty,
            ];
        }

        return array_slice($results, 0, $limit);
    }

    /**
     * تعداد «الأخوة» (siblings) لمجموعة قائمة طلبيات — نفس النافذة والنطاق وحالة soft-delete
     * كشرط findSimilar مع تجاهل الذات، بلا أي ترشيح للحالة. الهاتف فريد ضمن المتجر
     * (store+phone unique) لذا الأخوة على نفس الرقم == نفس customer_id.
     *
     * @param  array<int, string>  $orderIds
     * @param  string  $storeId
     * @return array<string, array{same_phone: int, same_product: int}>
     */
    public function countsBySiblings(array $orderIds, string $storeId): array
    {
        $orderIds = array_values(array_unique(array_filter(array_map('strval', $orderIds))));

        if (empty($orderIds) || empty($storeId)) {
            return [];
        }

        $since = Carbon::now()->subDays(self::WINDOW_DAYS);

        $pool = Order::query()
            ->where('store_id', $storeId)
            ->where('created_at', '>=', $since)
            ->with(['items:id,order_id,product_variant_id,product_id,quantity'])
            ->get(['id', 'customer_id']);

        $byCustomer = [];
        $poolItems = [];

        foreach ($pool as $order) {
            $cid = (string) $order->customer_id;
            $byCustomer[$cid][] = $order->id;

            $variants = [];
            $products = [];
            foreach ($order->items as $item) {
                if ($item->product_variant_id) {
                    $variants[] = (string) $item->product_variant_id;
                }
                if ($item->product_id) {
                    $products[] = (string) $item->product_id;
                }
            }
            $poolItems[$order->id] = [$variants, $products];
        }

        $targets = Order::query()
            ->whereIn('id', $orderIds)
            ->with(['items:id,order_id,product_variant_id,product_id,quantity'])
            ->get(['id', 'customer_id']);

        $counts = [];

        foreach ($targets as $target) {
            $samePhone = 0;
            $sameProduct = 0;

            $siblingIds = $byCustomer[(string) $target->customer_id] ?? [];

            if (! empty($siblingIds)) {
                [$targetVariants, $targetProducts] = $this->itemIds($target->items);

                foreach ($siblingIds as $siblingId) {
                    if ($siblingId === $target->id) {
                        continue;
                    }

                    $samePhone++;

                    [$siblingVariants, $siblingProducts] = $poolItems[$siblingId] ?? [[], []];

                    if (array_intersect($targetVariants, $siblingVariants) || array_intersect($targetProducts, $siblingProducts)) {
                        $sameProduct++;
                    }
                }
            }

            $counts[$target->id] = ['same_phone' => $samePhone, 'same_product' => $sameProduct];
        }

        return $counts;
    }

    /**
     * تعداد طلبياتٍ وصلت للإرسال (ناقل/موصّل مُسند أو حالة sent/delivered) لكل عميل ضمن
     * القائمة — لأجل إشارة «سبق أن طلب» المحايدة (بأي عمر؛ المستخدم حدد أن الفترة لا تهم).
     * تُرجع عددًا شامِلًا لكل customer_id (دون استثناء الذات — يستبعدها المتصل لأنه يملك
     * جاهزية الطلبية المعروضة). بلا ترشيح نافذة زمنية عمدًا.
     *
     * @param  array<int, string>  $customerIds
     * @return array<string, int>
     */
    public function countsPriorCarrierOrders(array $customerIds): array
    {
        $customerIds = array_values(array_unique(array_filter(array_map('strval', $customerIds))));

        if (empty($customerIds)) {
            return [];
        }

        $carrierKeys = \App\Domains\Order\Support\OrderWorkflow::carrier();

        $rows = Order::query()
            ->whereIn('customer_id', $customerIds)
            ->where(function ($q) use ($carrierKeys) {
                $q->whereNotNull('shipping_provider_id')
                    ->orWhereNotNull('delivery_rider_id')
                    ->orWhereHas('status', fn($sq) => $sq->whereIn('key', $carrierKeys));
            })
            ->selectRaw('customer_id, COUNT(*) as sibling_count')
            ->groupBy('customer_id')
            ->get();

        return $rows->mapWithKeys(fn($row) => [(string) $row->customer_id => (int) $row->sibling_count])->all();
    }

    /**
     * @param  \Illuminate\Support\Collection<int, \App\Models\Orders\OrderItem>  $items
     * @return array{0: list<string>, 1: list<string>}
     */
    private function itemIds(iterable $items): array
    {
        $variants = [];
        $products = [];

        foreach ($items as $item) {
            if ($item->product_variant_id) {
                $variants[] = (string) $item->product_variant_id;
            }
            if ($item->product_id) {
                $products[] = (string) $item->product_id;
            }
        }

        return [array_values(array_unique($variants)), array_values(array_unique($products))];
    }
}