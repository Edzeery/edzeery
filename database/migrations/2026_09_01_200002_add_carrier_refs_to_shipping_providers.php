<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Link a store's connected carrier (shipping_providers) to the platform-managed
     * carrier_platforms / carriers reference tables. Additive only — preserves the
     * existing orders.shipping_provider_id and the storefront cost calculator.
     */
    public function up(): void
    {
        Schema::table('shipping_providers', function (Blueprint $table) {
            $table->foreignUlid('carrier_platform_id')
                ->nullable()
                ->after('name')
                ->constrained('carrier_platforms')
                ->nullOnDelete();

            $table->foreignUlid('carrier_id')
                ->nullable()
                ->after('carrier_platform_id')
                ->constrained('carriers')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('shipping_providers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('carrier_id');
            $table->dropConstrainedForeignId('carrier_platform_id');
        });
    }
};
