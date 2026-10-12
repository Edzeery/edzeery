<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * PHASE 37-L: the original backfill (2026_10_11_000002) stamped only orders
 * that had at least one status-history row, leaving the rest NULL. The
 * sargable date window tolerates NULLs, but stamping them with the order's own
 * created_at keeps the (store_id, last_status_changed_at) index useful and
 * makes "last status date" mean the same thing for every row. Idempotent:
 * only rows still holding NULL are touched.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement(
            'UPDATE orders SET last_status_changed_at = created_at WHERE last_status_changed_at IS NULL'
        );
    }

    public function down(): void
    {
        // Nothing to undo: this only filled gaps the previous migration left.
    }
};
