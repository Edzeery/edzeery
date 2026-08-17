<?php

use App\Http\Middleware\Auth\PlatformRole;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\Merchant\Store\EnsureStoreMembership;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function (): void {
            // panel.php removed — all pages migrated to merchant Volt routes
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {

        $middleware->alias([
            'role' => PlatformRole::class,
            'merchant.access' => EnsureStoreMembership::class,
            'store.context' => \App\Http\Middleware\Api\EnsureStoreContext::class,
        ]);
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);
        $middleware->api(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);

        // لكل بوابة تسجيل دخول مستقلة:
        // - /super-admin → صفحة دخول موظفي المنصة
        // - الباقي → صفحة دخول التجار / المستخدمين
        $middleware->redirectGuestsTo(
            fn (Request $request) => str_starts_with($request->path(), 'super-admin')
                ? route('admin.login')
                : route('login')
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
