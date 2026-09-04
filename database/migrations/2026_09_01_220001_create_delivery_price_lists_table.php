<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Store-wide price lists (additive). A price list is NOT tied to a
     * specific delivery carrier — it is a store-level list of products that
     * share the same announced delivery prices (state home/office + per
     * municipality override). Storefront wiring is deferred; the data is
     * scoped by store_id so it can be consumed later.
     */
    public function up(): void
    {
        Schema::create('delivery_price_lists', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('store_id')
                ->constrained('stores')
                ->cascadeOnDelete();

            $table->string('name', 191);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index('store_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_price_lists');
    }
};
