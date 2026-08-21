<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('confirmation_shifts', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('membership_id')
                ->constrained('store_memberships')
                ->cascadeOnDelete();
            $table->string('shift_type');
            $table->time('start_time');
            $table->time('end_time');
            $table->json('days_of_week')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('membership_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('confirmation_shifts');
    }
};
