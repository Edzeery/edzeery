<?php

namespace App\Domains\Order\Services;

use App\Models\Orders\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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
     * Perf: the previous implementation hydrated the store's ENTIRE 30-day
     * order pool (plus every item) into PHP on every load; this version pushes
     * the counting into a grouped SQL query bounded by the targets' own
     * customers, so it stays fast regardless of store lifetime.
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

        // Only the visible rows are hydrated (their fingerprint comes from the
        // eager-loaded items), never the whole pool.
        $targets = Order::query()
            ->whereIn('id', $orderIds)
            ->with(['items:id,order_id,product_variant_id,product_id,quantity'])
            ->get(['id', 'customer_id']);

        $counts = [];

        foreach ($targets as $target) {
            $counts[$target->id] = ['same_phone' => 0, 'same_product' => 0];
        }

        $customerIds = $targets->pluck('customer_id')->filter()->map(fn ($id) => (string) $id)->unique()->values()->all();

        if (empty($customerIds)) {
            return $counts;
        }

        // Which of the targets fall inside the 30-day window themselves? Used
        // to subtract the self row from the grouped sibling count (the pool
        // includes every window order — targets included — minus self only).
        $selfInPool = Order::query()
            ->where('store_id', $storeId)
            ->where('created_at', '>=', $since)
            ->whereIn('id', $orderIds)
            ->pluck('id')
            ->flip();

        // same_phone: siblings sharing the same customer (phone) within the
        // window — one grouped count; self is subtracted below when present.
        $samePhoneByCustomer = Order::query()
            ->where('store_id', $storeId)
            ->where('created_at', '>=', $since)
            ->whereIn('customer_id', $customerIds)
            ->selectRaw('customer_id, COUNT(*) as c')
            ->groupBy('customer_id')
            ->pluck('c', 'customer_id');

        // Per-target fingerprint for the product-overlap part.
        $targetSets = [];
        $allVariants = [];
        $allProducts = [];

        foreach ($targets as $target) {
            [$variants, $products] = $this->itemIds($target->items);
            $targetSets[$target->id] = ['variants' => $variants, 'products' => $products];
            $allVariants = array_values(array_unique([...$allVariants, ...$variants]));
            $allProducts = array_values(array_unique([...$allProducts, ...$products]));
        }

        // Sibling items (same customers, same window) that share ANY target
        // product/variant — a single bounded query instead of hydrating the
        // pool's items. Targets themselves are part of the pool; each target
        // skips only itself while counting. Trashed orders are excluded
        // (matching the Eloquent SoftDeletes default of the former pool load).
        $poolByCustomer = [];

        if (! empty($allVariants) || ! empty($allProducts)) {
            $rows = DB::table('order_items as i')
                ->join('orders as o', 'o.id', '=', 'i.order_id')
                ->where('o.store_id', $storeId)
                ->whereNull('o.deleted_at')
                ->where('o.created_at', '>=', $since)
                ->whereIn('o.customer_id', $customerIds)
                ->where(function ($q) use ($allVariants, $allProducts) {
                    if (! empty($allVariants)) {
                        $q->whereIn('i.product_variant_id', $allVariants);
                    }
                    if (! empty($allProducts)) {
                        $q->orWhereIn('i.product_id', $allProducts);
                    }
                })
                ->select(['o.id as order_id', 'o.customer_id', 'i.product_variant_id', 'i.product_id'])
                ->get();

            foreach ($rows as $row) {
                $orderKey = (string) $row->order_id;
                $customerKey = (string) $row->customer_id;

                if (! isset($poolByCustomer[$customerKey])) {
                    $poolByCustomer[$customerKey] = [];
                }

                $poolByCustomer[$customerKey][$orderKey] ??= ['order_id' => $orderKey, 'items' => []];

                $poolByCustomer[$customerKey][$orderKey]['items'][] = [
                    'product_variant_id' => $row->product_variant_id !== null ? (string) $row->product_variant_id : null,
                    'product_id' => $row->product_id !== null ? (string) $row->product_id : null,
                ];
            }
        }

        foreach ($targets as $target) {
            $targetId = (string) $target->id;
            $customerKey = (string) $target->customer_id;

            $samePhone = (int) ($samePhoneByCustomer[$customerKey] ?? 0);

            // The grouped count includes the target itself when its order is
            // inside the 30-day window — subtract exactly the self row.
            if ($samePhone > 0 && isset($selfInPool[$targetId])) {
                $samePhone--;
            }

            $counts[$targetId]['same_phone'] = max(0, $samePhone);

            if (empty($poolByCustomer[$customerKey])) {
                continue;
            }

            $set = $targetSets[$targetId];
            $sameProduct = 0;

            foreach ($poolByCustomer[$customerKey] as $siblingId => $sibling) {
                if ($siblingId === $targetId) {
                    continue;
                }

                foreach ($sibling['items'] as $item) {
                    if ($item['product_variant_id'] !== null && in_array($item['product_variant_id'], $set['variants'], true)) {
                        $sameProduct++;
                        break;
                    }
                    if ($item['product_id'] !== null && in_array($item['product_id'], $set['products'], true)) {
                        $sameProduct++;
                        break;
                    }
                }
            }

            $counts[$targetId]['same_product'] = $sameProduct;
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