<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_trackings', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('store_id')
                ->constrained('stores')
                ->cascadeOnDelete();

            $table->foreignUlid('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            // Carrier used for THIS shipment attempt. May differ from
            // orders.shipping_provider_id if the order is re-shipped with
            // a different carrier after a return.
            $table->foreignUlid('shipping_provider_id')
                ->nullable()
                ->constrained('shipping_providers')
                ->nullOnDelete();

            $table->string('tracking_number')->nullable();
            $table->string('carrier_status')->nullable();   // raw code from carrier
            $table->string('carrier_label')->nullable();    // human-readable label

            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('returned_at')->nullable();

            $table->json('carrier_raw')->nullable();        // last raw API/webhook payload
            $table->timestamp('last_synced_at')->nullable();
            $table->string('webhook_token')->nullable()->unique();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['store_id', 'order_id']);
            $table->index('tracking_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_trackings');
    }
};
