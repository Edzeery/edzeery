<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('store_settings', 'allow_price_edit')) {
            return;
        }

        Schema::table('store_settings', function (Blueprint $table) {
            $table->boolean('allow_price_edit')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->dropColumn('allow_price_edit');
        });
    }
};