<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    public function switch(Request $request, $locale)
    {
        // تحقق أن اللغة مدعومة
        if (!in_array($locale, ['ar', 'en', 'fr', 'es'])) {
            $locale = config('app.locale');
        }

        session(['locale' => $locale]);
        setcookie('lang', $locale, time() + (365 * 24 * 60 * 60), '/');
        \Illuminate\Support\Facades\App::setLocale($locale);

        return response()->json(['status' => 'ok']);
    }
}
