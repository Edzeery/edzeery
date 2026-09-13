<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per shipping-company order weight ceiling (kg). The cap is decided by the
     * carrier that will carry the order; the merchant configures it on each
     * connected provider, defaulting to 50 kg when none is set.
     */
    public function up(): void
    {
        Schema::table('shipping_providers', function (Blueprint $table) {
            $table->decimal('max_weight_kg', 8, 3)->default(50.000)->after('is_default');
        });
    }

    public function down(): void
    {
        Schema::table('shipping_providers', function (Blueprint $table) {
            $table->dropColumn('max_weight_kg');
        });
    }
};