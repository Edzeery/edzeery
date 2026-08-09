<?php

use Illuminate\Support\Str;

if (!function_exists('icon')) {
    /**
     * جلب أيقونة حسب الاسم
     *
     * @param string $name
     * @param string|null $set
     * @param string|null $classes
     * @return string
     */
    function icon(string $name, string $set = null, string $classes = null): string
    {

        $config = config('icons');
        $set = $set ?: $config['default_set'];

        $icons = $config[$set] ?? [];
        $icon = $icons[$name] ?? $icons['default'] ?? null;

        if (!$icon) return '';

        $extra = $classes ? ' ' . $classes : '';

        if ($set === 'ion') {
            return "<ion-icon name=\"{$icon}\" class=\"{$classes}\"></ion-icon>";
        }

        if ($set === 'lucide') {
            return "<i data-lucide=\"{$icon}\" class=\"{$classes}\"></i>";
        }

        if ($set === 'hero') {
            return "<x-heroicon-o-{$icon} class=\"{$classes}\" />";
        }

        $prefix = $set === 'fa' ? 'fas' : ($set === 'bi' ? 'bi' : '');

        return "<i class=\"{$prefix} {$icon}{$extra}\"></i>";
    }
}


if (!function_exists('svg_icon')) {
    /**
     * جلب أيقونة SVG من resources/svg
     *
     * @param string $name اسم ملف SVG بدون الامتداد
     * @param string $classes أي كلاسات إضافية للعنصر <svg>
     * @return string|null
     */
    function svg_icon(string $name, string $classes = ''): ?string
    {
        $path = resource_path("svg/{$name}.svg");

        if (!file_exists($path)) {
            return null;
        }

        $svg = file_get_contents($path);

        // إضافة الكلاسات لو لم تكن موجودة
        if ($classes) {
            // أضف الكلاسات إلى وسم <svg>
            $svg = preg_replace(
                '/<svg([^>]*)>/i',
                '<svg$1 class="' . $classes . '">',
                $svg,
                1
            );
        }

        return $svg;
    }
}
if (!function_exists('getIconHtml')) {
    /**
     * جلب أيقونة سواء كانت من FontAwesome أو SVG
     *
     * @param string $name اسم الأيقونة
     * @param string|null $set نوع الأيقونات (fa أو bi أو svg)
     * @param string|null $classes كلاس إضافي (اختياري)
     * @return string كود HTML للأيقونة
     */
    function getIconHtml(string $name, ?string $set = null, ?string $classes = null): string
    {

        if (Str::startsWith($name, '<')) {
            return $name;
        }

        if ($set === 'svg') {
            $svg = svg_icon($name, $classes ?? '');
            return $svg ?? '';
        }

        return icon($name, $set, $classes ?? 'w-4 h-4');
    }
}

if (!function_exists('math_finance')) {
    /**
     * جلب مصطلح رياضي أو مالي مع الرمز
     *
     * @param string $key    المفتاح (sum, balance, amount, ...)
     * @param string|null $lang اللغة المطلوبة ('ar','en','fr'). إذا لم تُحدد، تستخدم اللغة الحالية تلقائيًا
     * @return array ['symbol' => '...', 'ar' => '...', 'en' => '...', 'fr' => '...']
     */
    function math_finance(string $key, ?string $lang = null): array
    {
        $data = config('math_finance_symbols', []);

        // إذا المفتاح غير موجود نعيد نسخة افتراضية
        if (!isset($data[$key])) {
            return [
                'symbol' => '',
                'ar'     => $key,
                'en'     => $key,
                'fr'     => $key,
                'es'     => $key,
            ];
        }

        $item = $data[$key];

        // التأكد من وجود كل المفاتيح
        $result = [
            'symbol' => $item['symbol'] ?? '',
            'ar'     => $item['ar'] ?? $key,
            'en'     => $item['en'] ?? $item['ar'] ?? $key,
            'fr'     => $item['fr'] ?? $item['ar'] ?? $key,
            'es'     => $item['es'] ?? $item['ar'] ?? $key,
        ];

        // استخدام لغة التطبيق الحالية إذا لم يتم تمرير لغة
        $lang = $lang ?: app()->getLocale();

        // نضمن أن المفتاح المطلوب موجود دائمًا
        if (!isset($result[$lang])) {
            $lang = 'ar';
        }

        return $result;
    }
}

//@php
//$symbolData = math_finance('sum'); // أو math_finance('balance', 'en')
//$language = app()->getLocale(); // ar, en, fr
//@endphp
//
//<span class="text-lg">{{ $symbolData['symbol'] }}</span>
//<span>{{ $symbolData[$language] }}</span>
