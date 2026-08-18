<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipping_providers', function (Blueprint $table) {
            $table->string('code')->nullable()->after('name');
            $table->boolean('is_default')->default(false)->after('is_active');
            $table->decimal('flat_rate', 10, 2)->nullable()->after('is_default');
        });

        Schema::create('shipping_rates', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('store_id')
                ->constrained('stores')
                ->cascadeOnDelete();

            $table->foreignUlid('shipping_provider_id')
                ->nullable()
                ->constrained('shipping_providers')
                ->nullOnDelete();

            $table->foreignUlid('state_id')
                ->nullable()
                ->constrained('states')
                ->nullOnDelete();

            $table->foreignUlid('city_id')
                ->nullable()
                ->constrained('cities')
                ->nullOnDelete();

            $table->string('label')->nullable();
            $table->decimal('cost', 10, 2);
            $table->unsignedInteger('free_above')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['store_id', 'state_id']);
        });

        Schema::create('stopdesk_points', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('store_id')
                ->constrained('stores')
                ->cascadeOnDelete();

            $table->foreignUlid('shipping_provider_id')
                ->nullable()
                ->constrained('shipping_providers')
                ->nullOnDelete();

            $table->foreignUlid('state_id')
                ->nullable()
                ->constrained('states')
                ->nullOnDelete();

            $table->foreignUlid('city_id')
                ->nullable()
                ->constrained('cities')
                ->nullOnDelete();

            $table->string('name');
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['store_id', 'state_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stopdesk_points');
        Schema::dropIfExists('shipping_rates');
        Schema::table('shipping_providers', function (Blueprint $table) {
            $table->dropColumn(['code', 'is_default', 'flat_rate']);
        });
    }
};
