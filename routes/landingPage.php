<?php

use App\Http\Controllers\Front\LandingPageController;
use Illuminate\Support\Facades\Route;

$domain = config('app.domain');
$wwwDomain = 'www.' . $domain;

Route::domain($domain)->group(function () {
    Route::get('/', [LandingPageController::class, 'index'])
        ->name('landing');

    Route::get('/contact-us', [LandingPageController::class, 'contact'])
        ->name('contact');
});

Route::domain($wwwDomain)->group(function () {
    Route::get('/', function () {
        return redirect()->route('landing');
    })->name('www.landing');

    Route::get('/{any}', function () {
        return redirect()->route('landing');
    })->where('any', '.*');
});
