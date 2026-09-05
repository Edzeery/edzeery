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
            ->with('status');

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
}