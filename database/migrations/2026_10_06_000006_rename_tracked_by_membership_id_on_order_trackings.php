<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PHASE 38-C.2 — name the tracking-row actor for what it means.
 *
 * `created_by_membership_id` on `order_trackings` is the member who acted to
 * start the shipment; it matches `orders.confirmed_by_membership_id` (the
 * confirmer), not `orders.created_by_membership_id` (who *entered* the order).
 * Rename it to `tracked_by_membership_id` so the two can never be confused.
 *
 * The committed 38-C migration (`2026_10_06_000004`) is left untouched; this
 * additive rename preserves the FK to `store_memberships` and its index on
 * both MySQL (native `RENAME COLUMN`) and SQLite (`RENAME COLUMN`), and is
 * reversible.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('order_trackings', 'created_by_membership_id')) {
            return;
        }

        Schema::table('order_trackings', function (Blueprint $table) {
            $table->renameColumn('created_by_membership_id', 'tracked_by_membership_id');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('order_trackings', 'tracked_by_membership_id')) {
            return;
        }

        Schema::table('order_trackings', function (Blueprint $table) {
            $table->renameColumn('tracked_by_membership_id', 'created_by_membership_id');
        });
    }
};
