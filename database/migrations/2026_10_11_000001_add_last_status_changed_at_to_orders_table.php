<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('last_status_changed_at')->nullable();

            $table->index(
                ['store_id', 'last_status_changed_at'],
                'orders_store_last_status_changed_at_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_store_last_status_changed_at_index');
            $table->dropColumn('last_status_changed_at');
        });
    }
};
