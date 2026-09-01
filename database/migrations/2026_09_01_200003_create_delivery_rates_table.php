<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Merchant-facing delivery pricing (additive). Each connect carrier (shipping_provider)
     * may define a state-level price pair: office cost (مكتب) and home cost (منزل).
     * Values can be entered manually and/or filled by a carrier API adapter (later phase).
     * The legacy shipping_rates table is intentionally left untouched so the storefront
     * cost calculator keeps working.
     */
    public function up(): void
    {
        Schema::create('delivery_rates', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('store_id')
                ->constrained('stores')
                ->cascadeOnDelete();

            $table->foreignUlid('shipping_provider_id')
                ->nullable()
                ->constrained('shipping_providers')
                ->nullOnDelete();

            $table->foreignUlid('state_id')
                ->constrained('states')
                ->cascadeOnDelete();

            $table->string('label')->nullable();
            $table->decimal('office_cost', 10, 2)->nullable();
            $table->decimal('home_cost', 10, 2)->nullable();
            $table->unsignedInteger('free_above')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique(['store_id', 'shipping_provider_id', 'state_id'], 'delivery_rate_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_rates');
    }
};
