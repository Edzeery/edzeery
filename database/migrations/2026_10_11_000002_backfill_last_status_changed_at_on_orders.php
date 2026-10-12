<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Stamp from the last history row; fall back to the order's own
        // creation date so no row is ever left NULL (the sargable window keeps
        // a NULL branch anyway, but a fully-stamped column keeps the
        // (store_id, last_status_changed_at) index doing the work).
        DB::statement(
            'UPDATE orders SET last_status_changed_at = COALESCE(('
            .'SELECT MAX(h.created_at) FROM order_status_histories h WHERE h.order_id = orders.id'
            .'), created_at)'
        );
    }

    public function down(): void
    {
        DB::table('orders')->update(['last_status_changed_at' => null]);
    }
};
