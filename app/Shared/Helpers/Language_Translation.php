<?php

use Illuminate\Support\Str;
use Outhebox\Translations\Models\Language;
use Outhebox\Translations\Models\Translation;

if (! function_exists('getLanguages')) {
    function getLanguages(): array|object
    {
        return Language::where('active', true)->get();
    }
}
function getLanguageCodes(): array
{
    return Language::where('active', true)
        ->pluck('code')
        ->toArray();
}

function getLanguageNames(): array
{
    return Language::where('active', true)
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
