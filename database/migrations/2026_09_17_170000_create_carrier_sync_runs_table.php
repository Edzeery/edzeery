<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Append-only metrics log for the NOEST tracking polling flow. One row per
     * syncProvider() run (per provider per run); read for pilot observability
     * only — never a source of truth for domain state.
     */
    public function up(): void
    {
        Schema::create('carrier_sync_runs', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('store_id')->constrained('stores')->cascadeOnDelete();
            $table->foreignUlid('shipping_provider_id')->constrained('shipping_providers')->cascadeOnDelete();
            $table->timestamp('started_at');
            $table->timestamp('finished_at');
            $table->unsignedInteger('attempted')->default(0);
            $table->unsignedInteger('updated')->default(0);
            $table->unsignedInteger('unknown')->default(0);
            $table->unsignedInteger('failed')->default(0);
            $table->timestamps();

            $table->index(['shipping_provider_id', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carrier_sync_runs');
    }
};