<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PHASE 38-C — first-write-wins actor + event timestamps on orders.
 *
 * All columns are nullable and additive. No backfill for existing orders:
 * old orders keep NULL stamps (unattributed/uncaptured) and the compensation
 * model must treat NULL as "not yet captured", never as zero.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('confirmed_at')->nullable();
            $table->foreignUlid('confirmed_by_membership_id')
                ->nullable()
                ->constrained('store_memberships', 'id')
                ->nullOnDelete();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('delivery_evidence_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->string('return_reason_key', 100)->nullable();

            // Compensation reads: who confirmed when (store-scoped), and which
            // orders were physically delivered in a window (store-scoped).
            $table->index(
                ['store_id', 'confirmed_by_membership_id', 'confirmed_at'],
                'orders_store_confirmed_by_at_index'
            );
            $table->index(
                ['store_id', 'delivered_at'],
                'orders_store_delivered_at_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_store_delivered_at_index');
            $table->dropIndex('orders_store_confirmed_by_at_index');
            $table->dropForeign(['confirmed_by_membership_id']);
            $table->dropColumn([
                'confirmed_at',
                'confirmed_by_membership_id',
                'delivered_at',
                'delivery_evidence_at',
                'returned_at',
                'return_reason_key',
            ]);
        });
    }
};