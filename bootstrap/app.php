<?php

use App\Http\Middleware\Auth\PlatformRole;
use App\Http\Middleware\SetLocale;
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

            // Storefront routes (domain-based: {store}.edzeery.com)
            require __DIR__ . '/../routes/storefront.php';
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {

        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'role' => PlatformRole::class,
            'store.context' => \App\Http\Middleware\Api\EnsureStoreContext::class,
            'resolve.store' => \App\Http\Middleware\ResolveStoreFromSubdomain::class,
            'store.locale' => \App\Http\Middleware\Store\SetStoreLocale::class,
        ]);
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);
        // SetLocale removed from api group — sessions are not started on API routes

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
