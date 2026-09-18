<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Flags an order (or tracking row) that only got its assignment because a
     * soft overflow above the team's max_concurrent_orders was allowed. Set by
     * the overflow enforcement logic in Phase 34.3 — schema groundwork only.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('over_capacity')
                ->default(false)
                ->after('assigned_by_membership_id');
        });

        Schema::table('order_trackings', function (Blueprint $table) {
            $table->boolean('over_capacity')
                ->default(false)
                ->after('assigned_by_membership_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('over_capacity');
        });

        Schema::table('order_trackings', function (Blueprint $table) {
            $table->dropColumn('over_capacity');
        });
    }
};