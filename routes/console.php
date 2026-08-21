<?php

use App\Domains\Order\Jobs\DispatchPendingAssignmentsJob;
use App\Domains\Order\Jobs\ShiftHandoverJob;
use App\Models\Stores\Store;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new DispatchPendingAssignmentsJob)->everyFifteenMinutes();

Schedule::call(function () {
    $stores = Store::where('status', 'active')->get();
    foreach ($stores as $store) {
        ShiftHandoverJob::dispatch($store);
    }
})->name('shift-handover')->everyFifteenMinutes()->withoutOverlapping();
