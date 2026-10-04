<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Every dashboard analytics block filters orders by store and window
     * (store_id + created_at). The only existing candidate is
     * orders_duplicate_scan (store_id, customer_id, created_at), whose
     * customer_id column sits between the two usable ones, so the planner has to
     * resolve it as a multi-column ANY() seek instead of a range scan.
     *
     * EXPLAIN QUERY PLAN on SQLite, 300 orders / 200 trackings:
     *   before: SEARCH orders USING INDEX orders_duplicate_scan
     *              (ANY(store_id) AND ANY(customer_id) AND created_at>? AND created_at<?)
     *   after:  SEARCH orders USING INDEX orders_store_id_created_at_index
     *              (store_id=? AND created_at>? AND created_at<?)
     *
     * No matching index was added for order_trackings: the delivery scope
     * subquery already resolves as
     * SEARCH order_trackings USING INDEX order_trackings_store_id_order_id_index
     * (store_id=? AND order_id=?), so a (order_id, assigned_to_membership_id)
     * index would only fold an already-cheap residual filter into the index.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['store_id', 'created_at'], 'orders_store_id_created_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_store_id_created_at_index');
        });
    }
};
