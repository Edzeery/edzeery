<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stopdesk_points', function (Blueprint $table) {
            // External office/station code as provided by the carrier API
            // (e.g. NOEST station_code). Null for merchant-managed desks.
            $table->string('external_code')->nullable()->after('phone');

            // Speed up the office cascade picker queries.
            $table->index(
                ['store_id', 'shipping_provider_id', 'state_id', 'city_id'],
                'stopdesk_points_provider_state_city_idx',
            );
        });

        // Idempotency guard for carrier-synced offices.
        Schema::table('stopdesk_points', function (Blueprint $table) {
            $table->unique(
                ['store_id', 'shipping_provider_id', 'external_code'],
                'stopdesk_points_provider_external_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::table('stopdesk_points', function (Blueprint $table) {
            $table->dropUnique('stopdesk_points_provider_external_unique');
            $table->dropIndex('stopdesk_points_provider_state_city_idx');
            $table->dropColumn('external_code');
        });
    }
};