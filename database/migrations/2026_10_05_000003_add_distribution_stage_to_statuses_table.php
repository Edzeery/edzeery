<?php

use App\Domains\Order\Support\OrderDistributionStage;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * PHASE 35.3 — distribution stage for order statuses.
 *
 * `distribution_stage` classifies what the order-distribution engine may do
 * with a status's assignment (confirmation / fulfillment / closed). It is
 * deliberately NOT `statuses.stage` (PHASE 38-C dashboard-KPI bucketing).
 *
 * The column is nullable: system order statuses are backfilled from
 * OrderDistributionStage, store-custom order statuses also resolve to
 * `confirmation`, and non-order statuses stay NULL (order-status consumers
 * treat NULL as `confirmation`, so a status created after this migration can
 * never be silently dropped from the confirmation pipeline).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('statuses', function (Blueprint $table) {
            $table->string('distribution_stage')
                ->nullable()
                ->after('movement_type');
        });

        DB::table('statuses')
            ->where('type', 'order')
            ->pluck('key', 'id')
            ->each(function (string $key, string $id): void {
                DB::table('statuses')
                    ->where('id', $id)
                    ->update(['distribution_stage' => OrderDistributionStage::forKey($key)]);
            });
    }

    public function down(): void
    {
        Schema::table('statuses', function (Blueprint $table) {
            $table->dropColumn('distribution_stage');
        });
    }
};
