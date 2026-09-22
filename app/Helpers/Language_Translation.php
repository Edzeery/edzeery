<?php

use Illuminate\Support\Str;
use Outhebox\Translations\Models\Language;
use Outhebox\Translations\Models\Translation;

if (! function_exists('activeLanguages')) {
    /**
     * قائمة اللغات النشطة بذاكرة مؤقتة لكل طلب.
     *
     * The '*', view composer passes getLanguages() to every rendered view,
     * so an uncached query here multiplies into hundreds of identical
     * `languages` queries per page load. The static cache is per-request
     * (PHP-FPM) and shares one collection across getLanguages(),
     * getLanguageCodes() and getLanguageNames().
     */
    function activeLanguages(): \Illuminate\Support\Collection
    {
        static $cache = null;

        if ($cache !== null) {
            return $cache;
        }

        return $cache = Language::where('active', true)->get();
    }
}

if (! function_exists('getLanguages')) {
    function getLanguages(): array|object
    {
        return activeLanguages();
    }
}
function getLanguageCodes(): array
{
    return activeLanguages()
        ->pluck('code')
        ->toArray();
}

function getLanguageNames(): array
{
    return activeLanguages()
        ->pluck('name')
        ->toArray();
}
function getLanguagesArray(): array
{
    return getLanguages()
        ->mapWithKeys(function ($lang) {
            return [
                $lang->code => __('general.' . Str::lower($lang->name))
            ];
        })
        ->toArray();
}
function getLanguagesArrayFlags(): array
{
    return getLanguages()
        ->mapWithKeys(function ($lang) {
            return [
                $lang->code => asset('images/icons/' . $lang->code . '.png')
            ];
        })
        ->toArray();
}



if (! function_exists('getCurrentLocale')) {
    function getCurrentLocale(): string
    {
        return str_replace('_', '-', app()->getLocale());
    }
}

if (! function_exists('isRTL')) {
    function isRTL($Locale = null): bool
    {
        if ($Locale === null) {
            return app()->getLocale() === 'ar';
        }

        $language = Language::where('code', $Locale)->get();

        return $language->rtl;
    }
}
if (! function_exists('setRTL')) {
    function setRTL(): string
    {
        return isRTL() ? 'rtl' : 'ltr';
    }
}

if (! function_exists('algin')) {
    function algin(): string
    {
        return isRTL() ? 'right' : 'left';
    }
}


if (!function_exists('translations')) {
    function translations()
    {

        return  Translation::with('language')->get();
    }
}

if (! function_exists('startSide')) {
    function startSide(string $size): string
    {
        return isRTL() ? "xl:mr-[$size]" : "xl:ml-[$size]";
    }
}

if (! function_exists('endSide')) {
    function endSide(string $size): string
    {
        return isRTL() ? "xl:ml-[$size]" : "xl:mr-[$size]";
    }
}

if (! function_exists('sidebarPosition')) {
    function sidebarPosition(): string
    {
        return isRTL() ? 'right-0' : 'left-0';
    }
}
