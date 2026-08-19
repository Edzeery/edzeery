<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('store_theme_settings', function ($table) {
            $table->json('section_content')->nullable()->after('homepage_sections');
        });
    }

    public function down(): void
    {
        Schema::table('store_theme_settings', function ($table) {
            $table->dropColumn('section_content');
        });
    }
};
