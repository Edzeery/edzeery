<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use App\Console\Commands\CheckSubscriptions;

class ConsoleServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->commands([
            CheckSubscriptions::class,
        ]);
    }

    public function boot()
    {
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);

            // جدولة التحقق يوميًا
            $schedule->command('subscriptions:check-expired')->daily();
        });
    }
}
