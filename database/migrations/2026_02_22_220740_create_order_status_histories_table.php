<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            $table->foreignUlid('status_id')
                ->constrained('statuses')
                ->restrictOnDelete();

            $table->foreignUlid('changed_by_membership_id')
                ->nullable()
                ->constrained('store_memberships')
                ->nullOnDelete();

            $table->text('reason')->nullable();

            $table->timestamps();

            $table->index(['order_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_status_histories');
    }
};
