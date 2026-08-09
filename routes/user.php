<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:user'])->group(function () {
    Route::get('/payments', fn() => 'User Dashboard');
});
