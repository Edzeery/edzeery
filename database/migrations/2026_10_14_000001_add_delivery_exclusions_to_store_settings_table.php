<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PHASE 37-N7 (D4) — the delivery rate exclusions, independent of the
 * confirmation ones.
 *
 * The delivery rate answers a different question than the confirmation rate, so
 * a store may keep a different set of status KEYS out of its sums. Like the
 * confirmation pair (PHASE 37-N2), the exclusions are a one-shot choice: the
 * first save stamps the lock and the columns are frozen at the model.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->json('delivery_excluded_status_keys')->nullable();
            $table->timestamp('delivery_exclusions_locked_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->dropColumn(['delivery_excluded_status_keys', 'delivery_exclusions_locked_at']);
        });
    }
};
