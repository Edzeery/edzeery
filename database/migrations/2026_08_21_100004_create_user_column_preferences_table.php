<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_column_preferences', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('membership_id')
                ->constrained('store_memberships')
                ->cascadeOnDelete();
            $table->string('view_key');
            $table->json('visible_columns');
            $table->timestamps();

            $table->unique(['membership_id', 'view_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_column_preferences');
    }
};
