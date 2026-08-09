<?php

namespace App\Support\Status;


class MathFinanceManager
{
    /**
     * الحصول على الرمز المختصر لأي مفتاح
     */
    public static function getSymbol(string $key): string
    {
        $symbols = config('math_finance_symbols', []);
        return $symbols[$key]['symbol'] ?? $key;
    }

    /**
     * الحصول على الاسم حسب اللغة (ar, en, fr)
     */
    public static function getName(string $key, string $lang = 'en'): string
    {
        $symbols = config('math_finance_symbols', []);
        return $symbols[$key][$lang] ?? $key;
    }

    /**
     * الحصول على كل البيانات
     */
    public static function get(string $key): array
    {
        $symbols = config('math_finance_symbols', []);
        return $symbols[$key] ?? [];
    }
}
