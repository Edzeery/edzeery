<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_riders', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('store_id')
                ->constrained('stores')
                ->cascadeOnDelete();

            $table->string('name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->string('vehicle_type')->default('motorcycle');
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['store_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_riders');
    }
};
