<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PHASE 39-M3 — the confirmation-rate denominator, per store.
 *
 * Holds the list of ConfirmationStatsGroups the store wants under the rate's
 * line (D4.1): null means "use the recommended default", confirmed asked over
 * the settled orders (confirmed + canceled + failed).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->json('confirmation_rate_denominator')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->dropColumn('confirmation_rate_denominator');
        });
    }
};
