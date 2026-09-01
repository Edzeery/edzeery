<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_column_preferences', function (Blueprint $table) {
            $table->string('table_style')->default('default')->after('visible_columns');
        });
    }

    public function down(): void
    {
        Schema::table('user_column_preferences', function (Blueprint $table) {
            $table->dropColumn('table_style');
        });
    }
};