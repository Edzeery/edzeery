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
        Schema::create('payments', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignUlid('store_id')
                ->nullable()
                ->constrained('stores')
                ->nullOnDelete();

            $table->foreignUlid('subscription_id')
                ->nullable()
                ->constrained('subscriptions')
                ->nullOnDelete();

            $table->foreignUlid('plan_price_id')
                ->constrained('plan_prices')
                ->cascadeOnDelete();

            $table->string('gateway')->default('chargily');
            $table->string('transaction_id')->nullable();

            $table->enum('status', [
                'pending',
                'paid',
                'failed',
                'refunded',
                'canceled',
            ])->default('pending');

            $table->decimal('amount', 10, 2);
            $table->string('currency', 10)->default('DZD');

            $table->json('meta')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index('status');
            $table->index('paid_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
