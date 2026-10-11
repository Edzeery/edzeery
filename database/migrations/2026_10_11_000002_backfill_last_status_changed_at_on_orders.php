<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(
            'UPDATE orders SET last_status_changed_at = ('
            .'SELECT MAX(h.created_at) FROM order_status_histories h WHERE h.order_id = orders.id'
            .')'
        );
    }

    public function down(): void
    {
        DB::table('orders')->update(['last_status_changed_at' => null]);
    }
};
