<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Marks which layout generation a user's column preference belongs to.
     * Rows created before the unified reorderable-columns rebuild have no
     * version, so they reset to the current defaults exactly once.
     */
    public function up(): void
    {
        Schema::table('user_column_preferences', function (Blueprint $table) {
            $table->unsignedSmallInteger('prefs_version')->nullable()->after('table_style');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_column_preferences', function (Blueprint $table) {
            $table->dropColumn('prefs_version');
        });
    }
};