<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Collection (التحصيل) + "authorization to open the packaging" support.
     *
     * - carriers: capability flags a carrier platform may advertise
     * - shipping_providers: per-store opt-in for a connected provider
     * - orders: per-order flags set when creating/editing an order
     */
    public function up(): void
    {
        Schema::table('carriers', function (Blueprint $table) {
            $table->boolean('supports_collection')->default(false)->after('supports_pickup');
            $table->boolean('supports_open_authorization')->default(false)->after('supports_collection');
        });

        Schema::table('shipping_providers', function (Blueprint $table) {
            $table->boolean('collection_enabled')->default(false)->after('shipment_types_enabled');
            $table->boolean('open_authorization_enabled')->default(false)->after('collection_enabled');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('is_collection')->default(false)->after('shipment_type');
            $table->boolean('authorized_to_open')->default(false)->after('is_collection');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['is_collection', 'authorized_to_open']);
        });

        Schema::table('shipping_providers', function (Blueprint $table) {
            $table->dropColumn(['collection_enabled', 'open_authorization_enabled']);
        });

        Schema::table('carriers', function (Blueprint $table) {
            $table->dropColumn(['supports_collection', 'supports_open_authorization']);
        });
    }
};