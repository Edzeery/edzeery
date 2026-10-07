<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PHASE 38-C — transition provenance on the status history.
 *
 * `from_status`: the key the order transitioned FROM (NULL on creation).
 * `source`: who/what triggered the transition
 * (manual|bulk|carrier|webhook|api|storefront|system|userscript), recorded so
 * the audit trail and the future ledger can attribute a move without guessing.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_status_histories', function (Blueprint $table) {
            $table->string('from_status')->nullable();
            $table->string('source')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('order_status_histories', function (Blueprint $table) {
            $table->dropColumn(['from_status', 'source']);
        });
    }
};