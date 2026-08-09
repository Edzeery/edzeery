<?php

use App\Http\Controllers\Account\ProfileController;
use App\Http\Controllers\Account\SecurityController;
use App\Http\Controllers\Account\SettingsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Merchant\DashboardController;
use App\Http\Controllers\Merchant\StoreController;
use App\Http\Controllers\Merchant\BillingController;
use App\Http\Controllers\Merchant\TeamController;

Route::prefix('account')
    ->middleware(['auth', 'verified']) // تأكد أن المستخدم مسجل دخول والتحقق من البريد
    ->name('account.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('merchant.dashboard');


        // Stores
        Route::get('/stores', [StoreController::class, 'index'])->name('stores');
        Route::get('/stores/create', [StoreController::class, 'create'])->name('stores.create');
        Route::get('/stores/{store}/edit', [StoreController::class, 'edit'])->name('stores.edit');

        // Billing / Subscriptions
        Route::get('/billing', [BillingController::class, 'index'])->name('billing');

        // Team
        Route::get('/team', [TeamController::class, 'index'])->name('team');
        Route::get('/team/create', [TeamController::class, 'create'])->name('team.create');
        Route::get('/team/{member}/edit', [TeamController::class, 'edit'])->name('team.edit');

        // Route::get('/profile', function () {
        //     return view('pages.profile', ['title' => 'Profile']);
        // })->name('profile');
        // // Profile


        Route::get('/profile', [ProfileController::class, 'index'])
            ->name('index');

        Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');

        Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');

        Route::post('/password', [SecurityController::class, 'updatePassword'])->name('password.update');
    });
