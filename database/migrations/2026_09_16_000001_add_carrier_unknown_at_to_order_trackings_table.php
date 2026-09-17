<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Records the last time a carrier sync proved this tracking number is
     * unknown on the carrier side (never created there, or deleted
     * off-platform). Null whenever the carrier still knows the number.
     * Cancellation reads this fact to complete a local cancel instead of
     * hard-failing on NOEST's "The given data was invalid." delete response.
     */
    public function up(): void
    {
        Schema::table('order_trackings', function (Blueprint $table) {
            $table->timestamp('carrier_unknown_at')->nullable()->after('last_synced_at');
            $table->index('carrier_unknown_at');
        });
    }

    public function down(): void
    {
        Schema::table('order_trackings', function (Blueprint $table) {
            $table->dropIndex('order_trackings_carrier_unknown_at_index');
            $table->dropColumn('carrier_unknown_at');
        });
    }
};