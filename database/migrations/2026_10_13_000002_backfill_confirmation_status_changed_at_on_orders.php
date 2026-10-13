<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // PHASE 37-N1 (D1): confirmation_status_changed_at is the "last
        // confirmation attempt" axis. A confirmed order is frozen at its
        // confirmed_at (first-write-wins, never restated when it later ships,
        // is delivered, returned or cancelled); an order that was never
        // confirmed holds its most recent status change, falling back to its
        // own creation date so no row is ever left NULL (mirrors the
        // last_status_changed_at backfill). MySQL- and SQLite-compatible.
        DB::statement(
            'UPDATE orders SET confirmation_status_changed_at = CASE '
            .'WHEN confirmed_at IS NOT NULL THEN confirmed_at '
            .'ELSE COALESCE(('
            .'SELECT MAX(h.created_at) FROM order_status_histories h WHERE h.order_id = orders.id'
            .'), created_at) END'
        );
    }

    public function down(): void
    {
        DB::table('orders')->update(['confirmation_status_changed_at' => null]);
    }
};
