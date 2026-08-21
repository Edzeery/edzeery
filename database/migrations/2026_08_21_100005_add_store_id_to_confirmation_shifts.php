<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('confirmation_shifts', function (Blueprint $table) {
            $table->foreignUlid('store_id')
                ->after('membership_id')
                ->constrained('stores')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('confirmation_shifts', function (Blueprint $table) {
            $table->dropForeign(['store_id']);
            $table->dropColumn('store_id');
        });
    }
};
