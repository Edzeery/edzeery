<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PHASE 37-N2 (D1/D2) — the confirmation-rate exclusions, per store.
 *
 * `confirmation_excluded_status_keys` is the list of status KEYS the store
 * wants removed from every term of the confirmation rate ("outside the rate").
 * `confirmation_exclusions_locked_at` freezes that choice: it is null until the
 * store saves its list once (an empty save locks too), after which the two
 * columns can never change again — enforced at the model, not just the UI.
 *
 * The old `confirmation_rate_denominator` column is left in place but is no
 * longer read; the gross/net pair replaced its preset.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->json('confirmation_excluded_status_keys')->nullable();
            $table->timestamp('confirmation_exclusions_locked_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->dropColumn(['confirmation_excluded_status_keys', 'confirmation_exclusions_locked_at']);
        });
    }
};
