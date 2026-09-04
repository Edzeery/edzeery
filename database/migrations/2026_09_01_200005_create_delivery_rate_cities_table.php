<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-municipality (بلديّة) home delivery prices, additive.
     * Each connect carrier (shipping_provider) + state may override the
     * state-level home price for each municipality (city/commune) belonging
     * to that state. The legacy shipping_rates table is left untouched.
     */
    public function up(): void
    {
        Schema::create('delivery_rate_cities', function (Blueprint $table) {
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

            $table->foreignUlid('city_id')
                ->constrained('cities')
                ->cascadeOnDelete();

            $table->decimal('home_cost', 10, 2)->nullable();
            $table->unsignedInteger('free_above')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique(['store_id', 'shipping_provider_id', 'state_id', 'city_id'], 'delivery_rate_city_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_rate_cities');
    }
};
