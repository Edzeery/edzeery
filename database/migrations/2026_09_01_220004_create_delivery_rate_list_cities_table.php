<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-municipality (بلديّة) home delivery price scoped to a store price
     * list. Overrides the list's state-level home cost when set.
     */
    public function up(): void
    {
        Schema::create('delivery_rate_list_cities', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('delivery_price_list_id')
                ->constrained('delivery_price_lists')
                ->cascadeOnDelete();

            $table->foreignUlid('state_id')
                ->constrained('states')
                ->cascadeOnDelete();

            $table->foreignUlid('city_id')
                ->constrained('cities')
                ->cascadeOnDelete();

            $table->decimal('home_cost', 10, 2)->nullable();

            $table->timestamps();

            $table->unique(['delivery_price_list_id', 'city_id'], 'delivery_rate_list_city_unique');
            $table->index(['delivery_price_list_id', 'state_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_rate_list_cities');
    }
};
