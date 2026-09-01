<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->json('meta')->nullable()->after('notes');
            $table->boolean('send_from_carrier_warehouse')->default(false)->after('shipment_type');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['meta', 'send_from_carrier_warehouse']);
        });
    }
};
