<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // orders.weight_kg must be able to represent the whole catalog weight
            // scale (product_variants.weight is decimal(8,3), i.e. up to 99999.999
            // kg) times any reasonable quantity. The former decimal(6,2) capped an
            // order at 9999.99 kg and crashed with SQLSTATE[22003] on legitimate
            // values; decimal(12,3) keeps catalog precision (kg with milligram DP).
            $table->decimal('weight_kg', 12, 3)->default('1.00')->change();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('weight_kg', 6, 2)->default('1.00')->change();
        });
    }
};