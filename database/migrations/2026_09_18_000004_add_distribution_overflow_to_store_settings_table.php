<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-store merchant controls for soft automatic distribution overflow
     * (tracking + confirmation alike), consumed by the overflow enforcement of
     * Phase 34.3. Range 0-100 is validated at the validation layer — no DB
     * check constraint (matches repo convention).
     */
    public function up(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->boolean('distribution_overflow_enabled')
                ->default(false)
                ->after('payment_methods');

            $table->unsignedTinyInteger('distribution_overflow_percentage')
                ->default(10)
                ->after('distribution_overflow_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->dropColumn([
                'distribution_overflow_enabled',
                'distribution_overflow_percentage',
            ]);
        });
    }
};