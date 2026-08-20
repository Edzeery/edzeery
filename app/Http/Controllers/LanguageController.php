<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;

class LanguageController extends Controller
{
    public function switch(Request $request, $locale)
    {
        if (!in_array($locale, ['ar', 'en', 'fr', 'es'])) {
            $locale = config('app.locale', 'en');
        }

        $request->session()->put('locale', $locale);
        Cookie::queue('lang', $locale, 60 * 24 * 365);
        App::setLocale($locale);

        return response()->json(['status' => 'ok', 'locale' => $locale]);
    }


}
