<?php

namespace App\Services\Stores;

use App\Domains\Status\StatusResolver;
use App\Enums\Store\OrderTrackingStatus;
use App\Models\Status;
use DomainException;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * قواعد التخصيص لكل متجر على صفوف `statuses` النظامية.
 *
 * المبدأ: لا نلمس صف النظام أبدًا — عند أي تعديل/ترتيب نُنشئ صف متجر بنفس
 * `(store_id, type, key)` ويتبعه StatusResolver تلقائيًا (أفضلية صف المتجر).
 * صفوف النظام تبقى مرجعًا للمخزون/الانتقالات في OrderService (دائمًا عبر
 * `Status::system()`)، لذا الـ override يؤثر على العرض فقط.
 *
 * اتفاقية i18n: `label=''` في صف المتجر تعني «أبقِ ترجمة status-kit» — يفسّرها
 * ResolvedStatus::fromModel كاختيار kit بدل نص المخزن.
 *
 * الحالات المخصّصة (addStatus):
 *  - تأكيد: `type='order'` مرتبطة بحالة أصلية (`linked_to`) ترث منها سمات
 *    المخزون/الانتقال/الأيقونة/نمط العرض.
 *  - راجل: `type='tracking'` مفاتيح حرة بذيل المتجر (لا تسرّب للشركات).
 */
class StoreStatusService
{
    public const TYPE = 'order';

    public const TRACKING_TYPE = 'tracking';

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
     * (تفضيل صف المتجر ثم النظامي) + حالات التأكيد المخصصة للمتجر (نهاية
     * القائمة)، مرتبة بموضع فعلي.
     *
     * @return array<int, array{key:string, sort_order:int, has_override:bool, override_label:string, color:string, is_custom:bool, linked_to:?string}>
     */
    public function confirmationList(string $storeId): array
    {
        return $this->listForType($storeId, self::TYPE, self::CONFIRMATION_KEYS);
    }

    /**
     * قائمة حالات تتبع الراجل لكل متجر: حالات enum الأساسية (11) + الحالات
     * المخصصة المضافة في صفحة التخصيص (لا تشمل مفاتيح الشركات إطلاقًا).
     *
     * @return array<int, array{key:string, sort_order:int, has_override:bool, override_label:string, color:string, is_custom:bool, linked_to:?string}>
     */
    public function riderList(string $storeId): array
    {
        $baseKeys = collect(OrderTrackingStatus::cases())->map->value->all();

        return $this->listForType($storeId, self::TRACKING_TYPE, $baseKeys);
    }

    public function saveLabel(string $storeId, string $key, string $label): Status
    {
        $this->assertEditable($storeId, self::TYPE, self::CONFIRMATION_KEYS, $key);

        return $this->writeOverride($storeId, self::TYPE, $key, ['label' => $label === '' ? '' : mb_substr($label, 0, 255)]);
    }

    public function saveColor(string $storeId, string $key, string $color): Status
    {
        $this->assertEditable($storeId, self::TYPE, self::CONFIRMATION_KEYS, $key);

        return $this->writeOverride($storeId, self::TYPE, $key, ['color' => $this->normalizeColor($color, $key)]);
    }

    public function riderSaveLabel(string $storeId, string $key, string $label): Status
    {
        $baseKeys = collect(OrderTrackingStatus::cases())->map->value->all();
        $this->assertEditable($storeId, self::TRACKING_TYPE, $baseKeys, $key);

        return $this->writeOverride($storeId, self::TRACKING_TYPE, $key, ['label' => $label === '' ? '' : mb_substr($label, 0, 255)]);
    }

    public function riderSaveColor(string $storeId, string $key, string $color): Status
    {
        $baseKeys = collect(OrderTrackingStatus::cases())->map->value->all();
        $this->assertEditable($storeId, self::TRACKING_TYPE, $baseKeys, $key);

        return $this->writeOverride($storeId, self::TRACKING_TYPE, $key, ['color' => $this->normalizeColor($color, $key)]);
    }

    /**
     * تحريك حالة موضعًا واحدًا في سلسلة التأكيد (dir = -1 للأعلى / +1 للأسفل).
     * يكتب صف override للمفاتيح التي غيّرت موضعها الفعلي فقط.
     */
    public function move(string $storeId, string $key, int $direction): void
    {
        $this->moveInList($storeId, $key, $direction, self::TYPE, $this->confirmationList($storeId));
    }

    /** نسخة الراجل من الحركة: تعمل على قائمة حالات تتبع الراجل فقط. */
    public function moveRider(string $storeId, string $key, int $direction): void
    {
        $this->moveInList($storeId, $key, $direction, self::TRACKING_TYPE, $this->riderList($storeId));
    }

    /**
     * إضافة حالة مخصصة للمتجر:
     *  - نوع 'order' (تأكيد) مرتبطة بحالة تأكيد أصلية (`linkedTo`) ترث منها
     *    سمات المخزون/الانتقال/الأيقونة/نمط العرض.
     *  - نوع 'tracking' (راجل) حالات حرة بلا ربط.
     */
    public function addStatus(string $storeId, string $type, string $label, string $color, ?string $linkedTo = null): Status
    {
        if (! in_array($type, [self::TYPE, self::TRACKING_TYPE], true)) {
            throw new InvalidArgumentException("Unsupported status type [$type].");
        }

        $label = trim($label);
        if ($label === '') {
            throw new InvalidArgumentException('A label is required for a new status.');
        }

        $color = $this->normalizeColor($color, $type);

        if ($type === self::TYPE && ! in_array($linkedTo, self::CONFIRMATION_KEYS, true)) {
            throw new InvalidArgumentException('A confirmation status must be linked to an original confirmation key.');
        }

        if ($type === self::TRACKING_TYPE) {
            $linkedTo = null;
        }

        $inherit = match (true) {
            $linkedTo !== null => $this->systemRowFor(self::TYPE, $linkedTo),
            default => null,
        };

        do {
            $key = $type.'_'.strtolower(Str::random(10));
        } while (Status::query()
            ->where('store_scope_id', $storeId)
            ->where('type', $type)
            ->where('key', $key)
            ->exists());

        $maxSort = (int) Status::query()
            ->where('type', $type)
            ->where(function ($query) use ($storeId) {
                $query->where('store_id', $storeId)->orWhereNull('store_id');
            })
            ->max('sort_order');

        $status = Status::query()->create([
            'store_id' => $storeId,
            'type' => $type,
            'key' => $key,
            'linked_to' => $linkedTo,
            'label' => mb_substr($label, 0, 255),
            'color' => $color,
            'is_system' => false,
            'affects_inventory' => (bool) ($inherit?->affects_inventory ?? false),
            'movement_type' => $inherit?->movement_type,
            'icon' => $inherit?->icon,
            'display_mode' => $inherit?->display_mode ?? 'badge',
            'sort_order' => $maxSort + 1,
        ]);

        StatusResolver::flush();

        return $status;
    }

    /** حذف حالة مخصصة للمتجر (مفتاح غير نظامي فقط). */
    public function deleteStatus(string $storeId, string $type, string $key): void
    {
        if (! in_array($type, [self::TYPE, self::TRACKING_TYPE], true)) {
            throw new InvalidArgumentException("Unsupported status type [$type].");
        }

        $custom = Status::query()
            ->where('store_id', $storeId)
            ->where('type', $type)
            ->where('key', $key)
            ->where('is_system', false)
            ->first();

        if ($custom === null) {
            throw new DomainException("No custom {$type} status [{$key}] to delete.");
        }

        $custom->delete();

        StatusResolver::flush();
    }

    /**
     * المفتاح السلوكي لحالة: إذا كان الحالة المخصصة مرتبطة بحالة تأكيد أصلية
     * (linked_to) نعيد الأصل، وإلا المفتاح نفسه — تُستخدم في بوابات الصلاحيات
     * وفحوص الانتقال لتعامل الحالة المخصصة كأصلها.
     */
    public function canonicalKey(string $storeId, string $key): string
    {
        $row = Status::query()
            ->where('store_id', $storeId)
            ->where('type', self::TYPE)
            ->where('key', $key)
            ->first();

        return filled($row?->linked_to) ? (string) $row->linked_to : $key;
    }

    /* ==========================
     | Internals
     ========================== */

    /**
     * قائمة نوع معين = صف لكل مفتاح أساسي (بأفضليّة المتجر ثم النظام) + الحالات
     * المخصصة للمتجر (نهاية القائمة).
     */
    protected function listForType(string $storeId, string $type, array $baseKeys): array
    {
        $rows = Status::query()
            ->where('type', $type)
            ->where(function ($query) use ($storeId) {
                $query->where('store_id', $storeId)->orWhereNull('store_id');
            })
            ->get()
            ->keyBy(fn (Status $row) => (empty($row->store_id) ? 'sys:' : 'store:').$row->key);

        $entries = [];

        foreach ($baseKeys as $key) {
            $storeRow = $rows->get('store:'.$key);
            $systemRow = $rows->get('sys:'.$key);

            if ($storeRow === null && $systemRow === null) {
                continue;
            }

            $entries[$key] = [
                'key' => $key,
                'sort_order' => $storeRow?->sort_order ?? $systemRow?->sort_order ?? 0,
                'has_override' => $storeRow !== null,
                'override_label' => $storeRow ? (string) $storeRow->label : '',
                'color' => $storeRow?->color ?? $systemRow?->color ?? 'gray',
                'is_custom' => false,
                'linked_to' => $storeRow?->linked_to,
            ];
        }

        foreach ($rows as $row) {
            if (empty($row->store_id) || array_key_exists($row->key, $entries)) {
                continue;
            }

            // تأكيد مخصص (order) يجب أن يكون مرتبطًا بحالة أصلية؛ راجل (tracking) أي مفتاح حر.
            if ($type === self::TYPE && empty($row->linked_to)) {
                continue;
            }

            $entries[$row->key] = [
                'key' => $row->key,
                'sort_order' => (int) $row->sort_order,
                'has_override' => true,
                'override_label' => (string) $row->label,
                'color' => $row->color ?? 'gray',
                'is_custom' => true,
                'linked_to' => $row->linked_to,
            ];
        }

        usort($entries, fn (array $a, array $b) => $a['sort_order'] <=> $b['sort_order']);

        return array_values($entries);
    }

    protected function moveInList(string $storeId, string $key, int $direction, string $type, array $list): void
    {
        if (! in_array($direction, [-1, 1], true)) {
            throw new InvalidArgumentException('Direction must be -1 or +1.');
        }

        $keys = array_column($list, 'key');
        $index = array_search($key, $keys, true);

        if ($index === false) {
            throw new DomainException("Move failed: unknown {$type} status [{$key}].");
        }

        $target = $index + $direction;
        if ($target < 0 || $target >= count($keys)) {
            return;
        }

        [$keys[$index], $keys[$target]] = [$keys[$target], $keys[$index]];

        $this->resequence($storeId, $type, $keys);
    }

    /** إعادة تسلسل positions إلى 1..N مع كتابة override فقط لكل مفتاح انحرف عن مصدره. */
    protected function resequence(string $storeId, string $type, array $orderedKeys): void
    {
        $rows = Status::query()
            ->where('type', $type)
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
                $this->writeOverride($storeId, $type, $key, ['sort_order' => $position]);
            }

            $position++;
        }
    }

    /**
     * إنشاء/تحديث صف متجر للمفتاح حاملًا أفضل القيم الموروثة من صف النظام
     * (أو من الحالة الأصلية link للحالة المخصصة) حتى لا يتغيّر سلوك العرض/المخزون.
     */
    protected function writeOverride(string $storeId, string $type, string $key, array $changes): Status
    {
        $existing = Status::query()
            ->where('type', $type)
            ->where('key', $key)
            ->where('store_id', $storeId)
            ->first();

        $inherit = match (true) {
            $existing !== null && ! empty($existing->linked_to) && $type === self::TYPE
                => $this->systemRowFor(self::TYPE, (string) $existing->linked_to),
            default => $this->systemRowFor($type, $key),
        };

        $values = array_merge([
            'label' => $existing?->label ?? '',
            'color' => $existing?->color ?? $inherit?->color ?? 'gray',
            'is_system' => false,
            'affects_inventory' => $inherit?->affects_inventory ?? false,
            'movement_type' => $inherit?->movement_type,
            'icon' => $inherit?->icon,
            'linked_to' => $existing?->linked_to,
            'display_mode' => $inherit?->display_mode ?? 'badge',
            'sort_order' => $existing?->sort_order ?? $inherit?->sort_order ?? 0,
        ], $changes);

        $override = Status::query()->updateOrCreate(
            [
                'store_id' => $storeId,
                'type' => $type,
                'key' => $key,
            ],
            $values
        );

        StatusResolver::flush();

        return $override;
    }

    protected function systemRowFor(string $type, string $key): ?Status
    {
        return Status::query()
            ->where('type', $type)
            ->where('key', $key)
            ->whereNull('store_id')
            ->first();
    }

    protected function assertEditable(string $storeId, string $type, array $baseKeys, string $key): void
    {
        if (in_array($key, $baseKeys, true)) {
            return;
        }

        $exists = Status::query()
            ->where('store_id', $storeId)
            ->where('type', $type)
            ->where('key', $key)
            ->exists();

        if (! $exists) {
            throw new InvalidArgumentException("[$key] is not an editable {$type} status key.");
        }
    }

    protected function normalizeColor(string $color, string $key): string
    {
        if (! in_array($color, self::COLOR_OPTIONS, true)) {
            throw new InvalidArgumentException("Invalid color [$color] for status [$key].");
        }

        return $color;
    }
}