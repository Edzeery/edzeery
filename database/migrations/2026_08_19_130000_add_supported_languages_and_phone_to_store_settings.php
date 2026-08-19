<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('store_settings', function ($table) {
            $table->json('supported_languages')->nullable()->after('language');
            $table->string('phone')->nullable()->after('timezone');
        });
    }

    public function down(): void
    {
        Schema::table('store_settings', function ($table) {
            $table->dropColumn(['supported_languages', 'phone']);
        });
    }
};
