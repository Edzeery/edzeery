<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PHASE 38-C — shipment-level capture.
 *
 * `created_by_membership_id`: the member who started the shipment (the same
 * actor threaded through startShipment/recordHistory today). A rider hand-off
 * created outside a member context stays NULL (unattributed).
 *
 * `cod_amount`: decimal(12,2) snapshot of the amount sent to the carrier for
 * COD orders, fixed at shipment creation (see OrderStatusCapture::codAmount,
 * which mirrors the carrier `montant` formula). Never updated afterwards —
 * a later change to the order's price must not rewrite what the carrier
 * collected for.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_trackings', function (Blueprint $table) {
            $table->foreignUlid('created_by_membership_id')
                ->nullable()
                ->constrained('store_memberships', 'id')
                ->nullOnDelete();
            $table->decimal('cod_amount', 12, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('order_trackings', function (Blueprint $table) {
            $table->dropForeign(['created_by_membership_id']);
            $table->dropColumn(['created_by_membership_id', 'cod_amount']);
        });
    }
};