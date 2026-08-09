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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignUlid('plan_id')
                ->nullable()
                ->constrained('plans')
                ->nullOnDelete();

            $table->foreignUlid('plan_price_id')
                ->constrained('plan_prices')
                ->cascadeOnDelete();

            $table->boolean('was_switched')->default(false);
            $table->boolean('is_trial')->default(false);

            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('suppressed_at')->nullable();

            $table->timestamp('grace_ends_at')->nullable();

            $table->enum('status', [
                'active',
                'pending',
                'expired',
                'canceled',
                'suspended',
            ])->default('pending');

            $table->softDeletes();
            $table->timestamps();

            $table->index('status');
            $table->index('ends_at');
        });

        Schema::create('subscription_renewals', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->boolean('overdue');
            $table->boolean('renewal');
            $table->foreignUlid('subscription_id')
                ->constrained('subscriptions')
                ->cascadeOnDelete();
            $table->timestamps();
        });


        Schema::create('feature_consumptions', function (Blueprint $table) {
            $table->id();

            $table->foreignUlid('subscription_id')
                ->constrained('subscriptions')
                ->cascadeOnDelete();

            $table->Decimal('consumption')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->foreignUlid('plan_feature_id')->constrained('plan_features')->cascadeOnDelete();

            $table->unique(['subscription_id', 'plan_feature_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feature_consumptions');
        Schema::dropIfExists('subscription_renewals');
        Schema::dropIfExists('subscriptions');
    }
};
