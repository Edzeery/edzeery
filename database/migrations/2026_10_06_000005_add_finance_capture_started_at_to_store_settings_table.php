<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * PHASE 38-C — compensation capture start, per store.
 *
 * Set to now() for every existing store by this migration: 38-B will read it
 * as the default `accrual_start_date` for the store's compensation plans, i.e.
 * compensation never starts before capture does.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->timestamp('finance_capture_started_at')->nullable();
        });

        DB::table('store_settings')
            ->whereNull('finance_capture_started_at')
            ->update(['finance_capture_started_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->dropColumn('finance_capture_started_at');
        });
    }
};