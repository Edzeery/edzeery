<?php

use App\Http\Controllers\Front\LandingPageController;
use Illuminate\Support\Facades\Route;

// routes/web.php
Route::get('/', [LandingPageController::class, 'index'])
    ->name('landing');

Route::get('/contact-us', [LandingPageController::class, 'contact'])
    ->name('contact');
