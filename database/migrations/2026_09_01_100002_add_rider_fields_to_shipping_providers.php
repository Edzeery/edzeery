<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipping_providers', function (Blueprint $table) {
            // Carrier-assigned rider information. Currently providers do not
            // expose this data, but we keep the columns reserved for the future
            // when it becomes available. They can be filled at any time from
            // the shipping provider edit form.
            $table->string('rider_name')->nullable()->after('flat_rate');
            $table->string('rider_phone')->nullable()->after('rider_name');
        });
    }

    public function down(): void
    {
        Schema::table('shipping_providers', function (Blueprint $table) {
            $table->dropColumn(['rider_name', 'rider_phone']);
        });
    }
};
