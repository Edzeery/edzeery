<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Carrier capability flags (schema only — real values are populated by
     * per-carrier data migrations so future carriers default safely to false),
     * plus the merchant-chosen subset of supported shipment types that a store
     * has enabled for a connected provider.
     */
    public function up(): void
    {
        Schema::table('carriers', function (Blueprint $table) {
            $table->boolean('supports_delivery')->default(false)->after('is_active');
            $table->boolean('supports_exchange')->default(false)->after('supports_delivery');
            $table->boolean('supports_pickup')->default(false)->after('supports_exchange');
            $table->boolean('supports_free_shipping_mode')->default(false)->after('supports_pickup');
            $table->boolean('supports_express_economic')->default(false)->after('supports_free_shipping_mode');
            $table->boolean('supports_api_notes')->default(false)->after('supports_express_economic');
            $table->boolean('supports_price_sync')->default(false)->after('supports_api_notes');
        });

        Schema::table('shipping_providers', function (Blueprint $table) {
            $table->json('shipment_types_enabled')->nullable()->after('credentials');
        });
    }

    public function down(): void
    {
        Schema::table('shipping_providers', function (Blueprint $table) {
            $table->dropColumn('shipment_types_enabled');
        });

        Schema::table('carriers', function (Blueprint $table) {
            $table->dropColumn([
                'supports_delivery',
                'supports_exchange',
                'supports_pickup',
                'supports_free_shipping_mode',
                'supports_express_economic',
                'supports_api_notes',
                'supports_price_sync',
            ]);
        });
    }
};
