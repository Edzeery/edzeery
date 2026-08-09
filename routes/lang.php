<?php

use App\Http\Controllers\LanguageController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;

Route::get('/js/lang/{locale}.js', function ($locale) {
    $path = resource_path("lang/{$locale}");

    if (!File::isDirectory($path)) {
        abort(404, "Language folder not found: {$locale}");
    }

    $files = File::files($path);
    $translations = [];

    foreach ($files as $file) {
        $filename = pathinfo($file, PATHINFO_FILENAME);
        $data = include $file->getRealPath();
        if (is_array($data)) {
            $translations[$filename] = $data;
        }
    }

    return response(
        'window.lang = ' . json_encode($translations, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . ';'
    )->header('Content-Type', 'application/javascript');
})->name('lang.js');


// Route::get('lang/{locale}', function ($locale) {
//     abort_unless(in_array($locale, ['ar', 'en', 'fr', 'es']), 404);
//     session(['locale' => $locale]);
//     return back();
// });

Route::post('/lang/{locale}', [LanguageController::class, 'switch'])->name('lang.switch');

