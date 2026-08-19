<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('store_theme_settings', function ($table) {
            $table->dropColumn('theme');
        });
    }

    public function down(): void
    {
        Schema::table('store_theme_settings', function ($table) {
            $table->string('theme')->default('default')->after('store_id');
        });
    }
};
