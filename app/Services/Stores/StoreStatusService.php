<?php

namespace App\Services\Stores;

use App\Domains\Status\StatusResolver;
use App\Models\Status;
use DomainException;
use InvalidArgumentException;

/**
 * قواعد التخصيص لكل متجر على صفوف `statuses` النظامية.
 *
 * المبدأ: لا نلمس صف النظام أبدًا — عند أي تعديل/ترتيب نُنشئ صف متجر بنفس
 * `(store_id, type='order', key)` ويتبعه StatusResolver تلقائيًا (أفضلية صف
 * المتجر). صفوف النظام تبقى مرجعًا للمخزون/الانتقالات في OrderService (دائمًا
 * عبر `Status::system()`)، لذا الـ override يؤثر على العرض فقط.
 *
 * اتفاقية i18n: `label=''` في صف المتجر تعني «أبقِ ترجمة status-kit» — يفسّرها
 * ResolvedStatus::fromModel كاختيار kit بدل نص المخزن.
 */
class StoreStatusService
{
    public const TYPE = 'order';

    /** بذرة Confirmation Pipeline كما في SystemStatusesSeeder (sort_order 1..10). */
    public const CONFIRMATION_KEYS = [
        'pending',
        'confirmed',
        'no_answer_1',
        'no_answer_2',
        'no_answer_3',
        'postponed',
        'wrong_number',
        'out_of_stock',
        'duplicate',
        'on_hold',
    ];

    /** ألوان قابلة للاختيار (مرآة مجموعة "general" في status-kit-statuses). */
    public const COLOR_OPTIONS = ['gray', 'success', 'warning', 'danger', 'info'];

    /**
     * قائمة سلسلة التأكيد لكل متجر: صف موحّد لكل مفتاح مع أفضل قيم مؤثرة
     * (تفضيل صف المتجر ثم النظامي)، مرتبة بموضع فعلي.
     *
     * @return array<int, array{key:string, sort_order:int, has_override:bool, override_label:string, color:string}>
     */
    public function confirmationList(string $storeId): array
    {
        $rows = Status::query()
            ->where('type', self::TYPE)
            ->whereIn('key', self::CONFIRMATION_KEYS)
            ->where(function ($query) use ($storeId) {
                $query->where('store_id', $storeId)->orWhereNull('store_id');
            })
            ->orderBy('sort_order')
            ->get()
            ->keyBy(fn (Status $row) => (empty($row->store_id) ? 'sys:' : 'store:').$row->key);

        $list = [];

        foreach (self::CONFIRMATION_KEYS as $key) {
            $storeRow = $rows->get('store:'.$key);
            $systemRow = $rows->get('sys:'.$key);

            if ($storeRow === null && $systemRow === null) {
                continue;
            }

            $list[] = [
                'key' => $key,
                'sort_order' => $storeRow?->sort_order ?? $systemRow?->sort_order ?? 0,
                'has_override' => $storeRow !== null,
                'override_label' => $storeRow ? (string) $storeRow->label : '',
                'color' => $storeRow?->color ?? $systemRow?->color ?? 'gray',
            ];
        }

        usort($list, fn (array $a, array $b) => $a['sort_order'] <=> $b['sort_order']);

        return array_values($list);
    }

    public function saveLabel(string $storeId, string $key, string $label): Status
    {
        $this->assertConfirmationKey($key);

        return $this->writeOverride($storeId, $key, ['label' => $label === '' ? '' : mb_substr($label, 0, 255)]);
    }

    public function saveColor(string $storeId, string $key, string $color): Status
    {
        $this->assertConfirmationKey($key);

        if (! in_array($color, self::COLOR_OPTIONS, true)) {
            throw new InvalidArgumentException("Invalid color [$color] for status [$key].");
        }

        return $this->writeOverride($storeId, $key, ['color' => $color]);
    }

    /**
     * تحريك حالة موضعًا واحدًا في سلسلة التأكيد (dir = -1 للأعلى / +1 للأسفل).
     * يكتب صف override للمفاتيح التي غيّرت موضعها الفعلي فقط.
     */
    public function move(string $storeId, string $key, int $direction): void
    {
        $this->assertConfirmationKey($key);
        if (! in_array($direction, [-1, 1], true)) {
            throw new InvalidArgumentException('Direction must be -1 or +1.');
        }

        $list = $this->confirmationList($storeId);
        $keys = array_column($list, 'key');
        $index = array_search($key, $keys, true);

        if ($index === false) {
            throw new DomainException("Move failed: unknown order status [$key].");
        }

        $target = $index + $direction;
        if ($target < 0 || $target >= count($keys)) {
            return;
        }

        [$keys[$index], $keys[$target]] = [$keys[$target], $keys[$index]];

        $this->resequence($storeId, $keys);
    }

    /** إعادة تسلسل positions إلى 1..N مع كتابة override فقط لكل مفتاح انحرف عن مصدره. */
    protected function resequence(string $storeId, array $orderedKeys): void
    {
        $rows = Status::query()
            ->where('type', self::TYPE)
            ->whereIn('key', $orderedKeys)
            ->get()
            ->groupBy('key');

        $position = 1;
        foreach ($orderedKeys as $key) {
            $group = $rows->get($key, collect());
            $storeRow = $group->first(fn (Status $row) => (string) $row->store_id === $storeId);
            $systemRow = $group->first(fn (Status $row) => empty($row->store_id));

            $sourceSort = $storeRow ? (int) $storeRow->sort_order : (int) ($systemRow?->sort_order ?? 0);

            if ($sourceSort !== $position) {
                $this->writeOverride($storeId, $key, ['sort_order' => $position]);
            }

            $position++;
        }
    }

    /**
     * إنشاء/تحديث صف متجر للمفتاح حاملًا أفضل القيم الموروثة من صف النظام
     * (لون/أيقونة/display/inventory) حتى لا يتغيّر سلوك العرض/المخزون.
     */
    protected function writeOverride(string $storeId, string $key, array $changes): Status
    {
        $systemRow = Status::query()
            ->where('type', self::TYPE)
            ->where('key', $key)
            ->whereNull('store_id')
            ->first();

        $existing = Status::query()
            ->where('type', self::TYPE)
            ->where('key', $key)
            ->where('store_id', $storeId)
            ->first();

        $values = array_merge([
            'label' => $existing?->label ?? '',
            'color' => $existing?->color ?? $systemRow?->color ?? 'gray',
            'is_system' => false,
            'affects_inventory' => $systemRow?->affects_inventory ?? false,
            'movement_type' => $systemRow?->movement_type,
            'icon' => $systemRow?->icon,
            'display_mode' => $systemRow?->display_mode ?? 'badge',
            'sort_order' => $existing?->sort_order ?? $systemRow?->sort_order ?? 0,
        ], $changes);

        $override = Status::query()->updateOrCreate(
            [
                'store_id' => $storeId,
                'type' => self::TYPE,
                'key' => $key,
            ],
            $values
        );

        StatusResolver::flush();

        return $override;
    }

    protected function assertConfirmationKey(string $key): void
    {
        if (! in_array($key, self::CONFIRMATION_KEYS, true)) {
            throw new InvalidArgumentException("[$key] is not a confirmation status key.");
        }
    }
}
