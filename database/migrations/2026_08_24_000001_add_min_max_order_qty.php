<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('min_order_qty')->nullable()->after('price');
            $table->unsignedInteger('max_order_qty')->nullable()->after('min_order_qty');
        });

        Schema::table('store_settings', function (Blueprint $table) {
            $table->unsignedInteger('min_order_qty')->nullable()->after('allow_backorder');
            $table->unsignedInteger('max_order_qty')->nullable()->after('min_order_qty');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['min_order_qty', 'max_order_qty']);
        });

        Schema::table('store_settings', function (Blueprint $table) {
            $table->dropColumn(['min_order_qty', 'max_order_qty']);
        });
    }
};
