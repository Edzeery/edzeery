<?php

use App\Domains\Status\Support\OrderStatusStage;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * PHASE 38-C — lifecycle stage for order statuses.
 *
 * `stage` must never be a second list of status keys: system defaults are
 * resolved through OrderStatusStage (which derives from DashboardStatusGroups),
 * store-custom statuses default to `other` until the merchant maps them in
 * PHASE 38-B. Backfill is structural (key → bucket), not historical — no actor
 * or timestamp backfill happens here or anywhere in 38-C.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('statuses', function (Blueprint $table) {
            $table->string('stage')
                ->default(OrderStatusStage::OTHER)
                ->after('movement_type');
        });

        DB::table('statuses')
            ->where('type', 'order')
            ->pluck('key', 'id')
            ->each(function (string $key, string $id): void {
                DB::table('statuses')
                    ->where('id', $id)
                    ->update(['stage' => OrderStatusStage::forKey($key)]);
            });

        // Non-order statuses (tracking / inventory / payment / shipment) get the
        // neutral bucket so the column is never NULL in practice. Order-status
        // hooks only ever read the stage of type='order' rows.
        DB::table('statuses')
            ->where('type', '!=', 'order')
            ->whereNull('stage')
            ->update(['stage' => OrderStatusStage::OTHER]);
    }

    public function down(): void
    {
        Schema::table('statuses', function (Blueprint $table) {
            $table->dropColumn('stage');
        });
    }
};