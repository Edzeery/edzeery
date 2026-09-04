<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pivot between a store price list and the products it applies to.
     * A product may belong to several lists (no uniqueness on product).
     * Composite primary key — Eloquent pivot sync does not hydrate an id.
     */
    public function up(): void
    {
        Schema::create('delivery_price_list_products', function (Blueprint $table) {
            $table->foreignUlid('delivery_price_list_id')
                ->constrained('delivery_price_lists')
                ->cascadeOnDelete();

            $table->foreignUlid('product_id')
                ->constrained('products')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->primary(['delivery_price_list_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_price_list_products');
    }
};
