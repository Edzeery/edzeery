<?php

namespace App\Domains\Status;

use App\Domains\Status\Support\ResolvedStatus;
use App\Models\Status as StatusModel;
use Edzeery\MyStatusKit\Facades\Status as StatusKit;

/**
 * دالة محلولة للحالات: أولوية قراءة صف المتجر من جدول statuses،
 * ثم الصف النظامي، ثم إعدادات status-kit، ثم fallback رمادي.
 */
class StatusResolver
{
    /** @var array<string, ResolvedStatus> */
    protected static array $cache = [];

    /** @var array<string, array<string, ResolvedStatus>> */
    protected static array $domainCache = [];

    public static function resolve(string $domain, string $key, ?string $storeId = null): ResolvedStatus
    {
        $cacheKey = static::cacheKey($domain, $key, $storeId);

        if (isset(static::$cache[$cacheKey])) {
            return static::$cache[$cacheKey];
        }

        $row = static::findModel($domain, $key, $storeId);

        if ($row !== null) {
            return static::$cache[$cacheKey] = ResolvedStatus::fromModel($row);
        }

        if (StatusKit::exists($domain, $key)) {
            return static::$cache[$cacheKey] = ResolvedStatus::fromKit(
                $domain,
                $key,
                StatusKit::for($domain, $key)
            );
        }

        return static::$cache[$cacheKey] = ResolvedStatus::fallback($domain, $key);
    }

    /**
     * حل كل حالات نطاق في استعلام واحد (صيغة select): قيم config + صفوف DB،
     * مع أفضليّة صف المتجر ثم الصف النظامي.
     *
     * @return array<string, ResolvedStatus>
     */
    public static function domain(string $domain, ?string $storeId = null): array
    {
        $cacheKey = static::domainCacheKey($domain, $storeId);

        if (isset(static::$domainCache[$cacheKey])) {
            return static::$domainCache[$cacheKey];
        }

        $rowsByKey = static::allModelsByKey($domain, $storeId);
        $resolved = [];

        foreach (StatusKit::domain($domain) as $key => $kitResult) {
            $resolved[$key] = self::preferRow($rowsByKey[$key] ?? [], $storeId)
                ?? ResolvedStatus::fromKit($domain, $key, $kitResult);
        }

        foreach ($rowsByKey as $key => $rowGroup) {
            if (isset($resolved[$key])) {
                continue;
            }

            $row = self::preferRow($rowGroup, $storeId);

            if ($row !== null) {
                $resolved[$key] = ResolvedStatus::fromModel($row);
            }
        }

        return static::$domainCache[$cacheKey] = $resolved;
    }

    /** حذف ذاكرة التخزين المؤقتة (اختبارات / بيانات جديدة). */
    public static function flush(): void
    {
        static::$cache = [];
        static::$domainCache = [];
    }

    protected static function cacheKey(string $domain, string $key, ?string $storeId): string
    {
        return implode('|', [$domain, $key, $storeId ?? 'system']);
    }

    protected static function domainCacheKey(string $domain, ?string $storeId): string
    {
        return implode('|', ['domain', $domain, $storeId ?? 'system']);
    }

    /** كل صفوف النطاق (specific للمتجر والساعاتية) مجمّعة بالـ key. */
    protected static function allModelsByKey(string $domain, ?string $storeId): array
    {
        $rowsByKey = [];

        try {
            $rows = StatusModel::query()
                ->where('type', $domain)
                ->where(function ($query) use ($storeId) {
                    if ($storeId !== null) {
                        $query->where('store_id', $storeId);
                    }
                    $query->orWhereNull('store_id');
                })
                ->orderBy('sort_order')
                ->get();

            foreach ($rows as $row) {
                $rowsByKey[$row->key][] = $row;
            }
        } catch (\Illuminate\Database\QueryException) {
            return [];
        }

        return $rowsByKey;
    }

    protected static function preferRow(array $rows, ?string $storeId): ?StatusModel
    {
        if ($storeId !== null) {
            foreach ($rows as $row) {
                if ((string) $row->store_id === (string) $storeId) {
                    return $row;
                }
            }
        }

        return $rows[0] ?? null;
    }

    protected static function findModel(string $domain, string $key, ?string $storeId): ?StatusModel
    {
        try {
            if ($storeId !== null) {
                $row = StatusModel::query()
                    ->where('type', $domain)
                    ->where('key', $key)
                    ->where('store_id', $storeId)
                    ->first();

                if ($row !== null) {
                    return $row;
                }
            }

            return StatusModel::query()
                ->where('type', $domain)
                ->where('key', $key)
                ->whereNull('store_id')
                ->first();
        } catch (\Illuminate\Database\QueryException) {
            return null;
        }
    }
}
