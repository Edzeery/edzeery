<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // The store-owned rider assigned to deliver this order.
            $table->foreignUlid('delivery_rider_id')
                ->nullable()
                ->constrained('delivery_riders')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('delivery_rider_id');
        });
    }
};
