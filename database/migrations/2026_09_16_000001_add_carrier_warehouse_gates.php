<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The "send from carrier warehouse" lane needs its own capability plane:
     * a structure flag on carriers (whether the platform offers the warehouse
     * lane at all) plus a merchant opt-in per connected provider, mirroring the
     * refund_request / can_open planes. Feature visibility is then derived by
     * CarrierFeatureService (API capabilities ∧ structure ∧ opt-in).
     */
    public function up(): void
    {
        Schema::table('carriers', function (Blueprint $table) {
            $table->boolean('supports_send_from_carrier_warehouse')->default(false)->after('supports_pickup');
        });

        Schema::table('shipping_providers', function (Blueprint $table) {
            $table->boolean('send_from_carrier_warehouse_enabled')->default(false)->after('can_open_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('shipping_providers', function (Blueprint $table) {
            $table->dropColumn('send_from_carrier_warehouse_enabled');
        });

        Schema::table('carriers', function (Blueprint $table) {
            $table->dropColumn('supports_send_from_carrier_warehouse');
        });
    }
};