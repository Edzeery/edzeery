<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Dispatch handover validation (NOEST /valid/order flow), tracked on the
     * order_trackings row. Deliberately separate from the RETURNS/rotor
     * inspection columns (verification_barcode / verified_at / inspection_*)
     * which represent physically different events.
     */
    public function up(): void
    {
        Schema::table('order_trackings', function (Blueprint $table) {
            $table->timestamp('carrier_validated_at')->nullable()->after('requeued_by_membership_id');
            $table->foreignUlid('carrier_validated_by_membership_id')
                ->nullable()
                ->after('carrier_validated_at')
                ->constrained('store_memberships')
                ->nullOnDelete();
            $table->string('carrier_validation_error')->nullable()->after('carrier_validated_by_membership_id');

            $table->index('carrier_validated_at');
        });
    }

    public function down(): void
    {
        Schema::table('order_trackings', function (Blueprint $table) {
            $table->dropForeign(['carrier_validated_by_membership_id']);
            $table->dropColumn([
                'carrier_validated_at',
                'carrier_validated_by_membership_id',
                'carrier_validation_error',
            ]);
        });
    }
};