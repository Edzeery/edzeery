<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * State-level home/office delivery prices scoped to a store price list.
     * Kept separate from delivery_rates (carrier-scoped) so both concepts
     * stay additive and isolated.
     */
    public function up(): void
    {
        Schema::create('delivery_rate_list_states', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('delivery_price_list_id')
                ->constrained('delivery_price_lists')
                ->cascadeOnDelete();

            $table->foreignUlid('state_id')
                ->constrained('states')
                ->cascadeOnDelete();

            $table->decimal('home_cost', 10, 2)->nullable();
            $table->decimal('office_cost', 10, 2)->nullable();

            $table->timestamps();

            $table->unique(['delivery_price_list_id', 'state_id'], 'delivery_rate_list_state_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_rate_list_states');
    }
};
