<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Extend merchant delivery pricing (additive).
     * - default_center_id: the store's pickup point used as the default center
     *   (office) for a carrier + state pair.
     * - source: where the state-level prices came from ('manual' | 'announced').
     * - synced_at: last time prices were pulled from the carrier API.
     */
    public function up(): void
    {
        Schema::table('delivery_rates', function (Blueprint $table) {
            $table->foreignUlid('default_center_id')
                ->nullable()
                ->after('free_above')
                ->constrained('stopdesk_points')
                ->nullOnDelete();

            $table->string('source', 20)->default('manual')->after('default_center_id');
            $table->timestamp('synced_at')->nullable()->after('source');
        });
    }

    public function down(): void
    {
        Schema::table('delivery_rates', function (Blueprint $table) {
            $table->dropConstrainedForeignId('default_center_id');
            $table->dropColumn(['source', 'synced_at']);
        });
    }
};
