<?php

use App\Http\Middleware\Auth\PlatformRole;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\Merchant\Store\EnsureStoreMembership;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function (): void {
            require __DIR__ . '/../routes/panel.php';
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
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
