<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['store_id', 'customer_id', 'created_at'], 'orders_duplicate_scan');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->index(['product_variant_id'], 'order_items_variant_lookup');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_duplicate_scan');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex('order_items_variant_lookup');
        });
    }
};