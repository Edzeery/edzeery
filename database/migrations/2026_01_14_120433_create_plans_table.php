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
        Schema::create('plan_features', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique(); // products_limit, staff_limit
            $table->string('type')->default('number'); // number, boolean
            $table->string('unit')->nullable(); // products, users
            $table->text('description')->nullable();

            $table->boolean('consumable')->default(false);
            $table->boolean('quota')->default(false);
            $table->unsignedInteger('periodicity')->nullable();
            $table->string('periodicity_type')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('plans', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();

            $table->integer('trial_days')->default(0); // ✔️ هنا مكانها
            $table->integer('max_stores')->default(1); // ✔️ multi-store

            $table->foreignUlid('upgrade_to_plan_id')->nullable()
                ->constrained('plans')
                ->nullOnDelete(); // ✔️ ترقية تلقائية

            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->string('currency', 10)->default('DZD');
            $table->integer('sort_order')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('plan_prices', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('plan_id')->constrained('plans')->cascadeOnDelete();

            $table->enum('billing_period', ['monthly', 'yearly']);
            $table->decimal('price', 10, 2);
            $table->string('currency', 10)->default('DZD');
            $table->integer('duration')->comment('days'); // 30 / 365

            $table->timestamps();
            $table->boolean('is_active')->default(true);
            $table->unique(['plan_id', 'billing_period']);
        });

        Schema::create('plan_plan_feature', function (Blueprint $table) {
            $table->id('id');
            $table->foreignUlid('plan_id')->constrained('plans')->cascadeOnDelete();
            $table->foreignUlid('plan_feature_id')->constrained('plan_features')->cascadeOnDelete();

            $table->string('value')->nullable(); // 100, unlimited, true
            $table->decimal('charges')->nullable();
            $table->timestamps();

            $table->unique(['plan_id', 'plan_feature_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_plan_feature');

        Schema::dropIfExists('plan_prices');
        Schema::dropIfExists('plans');
        Schema::dropIfExists('plan_features');
    }
};
